# Audit initial

## 1. Comportement observable

Index.php : On voit le moyen de paiement aini que le montant payée par l'utilisateur. On voit la confirmation de l'enregistrement en bdd du numéro de reservation avec le montant payé. Confirmation de l'envoie par mail du numéro de reservation avec l'email du client + son numéro de reservation et pour finir, affichage du montant total final.


tests/characterization.php : on voit le nombre de test réussis et ratés

## 2. Problèmes identifiés

| # | Problème | Catégorie | Impact |
|---|---|---|---|
| 1 | OCP non respecté dans BookingService.php | Duplication | Important |
| 2 | Variables magiques et constantes en clair dans BookingService.php | Règle métier | Important |
| 3 | Naming des fonctions dans BookingService.php, TestRunner.php | Lisibilité | Très Important |
| 4 | Pas de garde fou contre les totaux négatifs | Règles métier | Critique |
| 5 | SRP non respecté | Responsabilité | Critique |
| 6 | Primitive obsession | Règles métiers | Important |

## 3. Nos trois priorités

1.Les gardes fous, bug financier critique et risque de crash de la passerelle de paiement
2.SRP non respecté, Surcharge de BookingService::confirm() qui rend la méthode fragile et complexe a maintenir
3.OCP non respecté, le mode de paiement et les notifications sont codés en dur via des conditions ce qui oblige à modifier directement le code existant, augmentant fortement le risque de régression.

## 4. Risques avant refactoring

1. Peut mener à des erreurs et refus de la part des prestataires de paiement. Peut aussi rendre faux les statistiques financieres et sur les resultats de l'entreprise.
2. Délais plus long avant de regler un problème concernant cette partie du code.
3. Introduire des problèmes globaux lorsqu'on veut faire un simple ajout.
