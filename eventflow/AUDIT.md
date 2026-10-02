# Audit initial

## 1. Comportement observable

Index.php : On voit le moyen de paiement aini que le montant payée par l'utilisateur. On voit la confirmation de l'enregistrement en bdd du numéro de reservation avec le montant payé. Confirmation de l'envoie par mail du numéro de reservation avec l'email du client + son numéro de reservation et pour finir, affichage du montant total final.


tests/characterization.php : on voit le nombre de test réussis et ratés

## 2. Problèmes identifiés

| # | Problème | Catégorie | Impact |
|---|---|---|---|
| 1 | OCP non respecté dans BookingService.php | Duplication | Important |
| 2 | Variables magiques et constantes en clair dans BookingService.php | Règle métier | Important |
| 3 |  |  |  |
| 4 |  |  |  |
| 5 |  |  |  |
| 6 |  |  |  |

## 3. Nos trois priorités

1.
2.
3.

## 4. Risques avant refactoring

À compléter.
