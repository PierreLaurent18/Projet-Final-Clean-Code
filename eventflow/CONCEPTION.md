# Note de conception

## 1. Choix principaux

1. Découplage strict de `BookingService` par injection de dépendances** : le service central a été déchargé de toutes ses responsabilités techniques et algorithmiques pour devenir un simple orchestrateur de cas d'usage.
2. Abstractions des services d'infrastructure : création d'interfaces dédiées pour le paiement (`PaymentGatewayInterface`), la persistance (`BookingRepositoryInterface`) et la notification (`MailerInterface`).
3. Encapsulation du modèle de domaine : rapatriement des règles de validation et des calculs élémentaires dans les entités du domaine (`Booking`, `BookingItem`) selon le principe Tell, Don't Ask.
4. Conservation de la compatibilité ascendante : utilisation d'arguments optionnels avec instanciations par défaut dans le constructeur de `BookingService` pour ne pas casser le point d'entrée historique (`index.php`) tout en autorisant l'injection de doublures de test.

## 2. Principes SOLID mobilisés

Pour chaque principe réellement utilisé :
- problème initial ;
- classes concernées ;
- bénéfice obtenu.

Single Responsibility Principle : 
- Problème initial : `BookingService` accumulait la validation des données d'entrée, l'algorithme tarifaire (remises, forfaits), les appels directs aux passerelles de paiement, les requêtes SQL simulées et l'envoi d'e-mails.
- Classes concernées : `BookingService`, `Booking`, `BookingItem`, `SqlBookingRepository`, `FestivalPricingStrategy`.
- Bénéfice obtenu : chaque classe n'a plus qu'une seule raison de changer. La validation appartient à `Booking`, le calcul du prix à la stratégie, la persistance au repository, et `BookingService` ne s'occupe que de coordonner le flux.

Open/Closed Principle :
- Problème initial : l'ajout d'une nouvelle passerelle de paiement ou d'un mode de tarification alternatif (ex. tarif entreprise ou pass 1 jour spécifique) imposait de modifier le code interne de `BookingService`.
- Classes concernées : `BookingService`, `PaymentGatewayInterface`, `PricingStrategyInterface`.
- Bénéfice obtenu : le système peut intégrer un nouveau prestataire de paiement ou une nouvelle logique tarifaire par simple création d'une nouvelle classe implémentant l'interface cible, sans toucher à une seule ligne de `BookingService`.

Dependency Inversion Principle :

- Problème initial : `BookingService` dépendait directement de concrétions d'infrastructure et de SDK externes (`new StripeClient()`, `new EmailService()`), interdisant le remplacement des modules et rendant les tests unitaires impossibles sans effets de bord réels.
- Classes concernées : `BookingService`, `PaymentGatewayInterface`, `BookingRepositoryInterface`, `MailerInterface`.
- Bénéfice obtenu : les modules de haut niveau (métier) ne dépendent plus des modules de bas niveau (infrastructure) ; tous deux dépendent d'abstractions (interfaces PHP).

## 3. Design Patterns éventuellement utilisés

Pour chaque pattern :
- problème rencontré ;
- solution retenue ;
- pourquoi une solution plus simple ne suffisait pas.

Si aucun pattern n'est utilisé sur une partie du projet, expliquez pourquoi.

### Pattern Strategy
- Problème rencontré : la tarification du festival mêle déductions fixes (pass 3 jours) et paliers de remise VIP non linéaires. Ce calcul polluait le flux de réservation et devait pouvoir être substitué par d'autres politiques de prix.
- Solution retenue : création de `PricingStrategyInterface` et de son implémentation `FestivalPricingStrategy`, intégrant un garde-fou (`max(0.0, $total)`).
- Pourquoi une solution plus simple ne suffisait pas : une simple méthode privée dans `BookingService` ou une fonction globale maintenait un couplage fort, interdisait le changement de stratégie au runtime et compliquait l'écriture de tests isolés sur les cas limites de tarification.

### Pattern Adapter
- Problème rencontré : intégrer deux passerelles de paiement externes (`StripeClient` et `PayFastSdk`) aux interfaces et signatures incompatibles (noms de méthodes, formats d'arguments, identifiants retournés différents), sans pouvoir modifier le code source de ces SDK tiers.
- Solution retenue : définition d'un contrat cible `PaymentGatewayInterface` (`charge(float $amount): string`) et de deux adaptateurs dédiés : `StripePaymentAdapter` et `PayFastPaymentAdapter`.
- Pourquoi une solution plus simple ne suffisait pas : insérer des conditions `if/else` directement dans `BookingService` pour inspecter le type de client de paiement aurait réintroduit du couplage fort et violé le principe OCP à chaque nouveau prestataire.

### Pattern Repository
- Problème rencontré : l'instruction de persistance SQL était codée en dur dans `BookingService`, empêchant toute portabilité du stockage (mémoire, base relationnelle, document) et polluant la logique métier avec des détails d'infrastructure.
- Solution retenue : mise en place de `BookingRepositoryInterface` avec l'implémentation concrète `SqlBookingRepository`.
- Pourquoi une solution plus simple ne suffisait pas : un simple appel direct à une fonction ou un helper SQL liait irrévocablement le cas d'usage métier à un dialecte de stockage particulier et bloquait la création de mocks pour les tests.

## 4. Solutions envisagées puis écartées

* Conteneur d'injection de dépendances (DIC) : rejeté car surdimensionné pour le périmètre du projet. L'injection manuelle par constructeur suffit et évite d'ajouter une dépendance tierce.
* Pattern Observer / Event Dispatcher pour les notifications : écarté au profit d'un simple `MailerInterface` direct afin de limiter la complexité et le niveau d'indirection.
* Value Object `Money` : abandonné temporairement pour éviter un refactoring transversal trop lourd sur les entités et SDK tiers, risquant d'introduire des régressions.

## 5. Ce que nous améliorerions avec plus de temps

Architecture événementielle (PSR-14) : découpler l'ensemble des effets de bord (e-mail, SMS, fidélité, analytics) via un événement `BookingConfirmed`.
Immutabilité et encapsulation du domaine : rendre les propriétés de `Booking` privées et encapsuler les transitions d'état (`$booking->confirm()`).
Gestion monétaire précise : manipuler des montants entiers en centimes plutôt que des `float` pour bannir tout risque d'arrondi flottant.
Gestion transactionnelle : sécuriser le paiement et la persistance dans une transaction avec mécanisme de remboursement en cas d'échec SQL.
