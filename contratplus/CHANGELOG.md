# <span style='color:white;background-color:#ed6b00;border-radius:5px;padding: 5px;font-size:small'>Nouveautés</span> ChangeLog pour [v.12.0.2]
* Corrections :
  * Ajout des icônes de renouvellement automatiques + note privée sur le listing contrat plus suite au reformatage
  * Suppression d'un warning sur un Dolibarr 7

---

# ChangeLog pour [v.12.0.1]
* Améliorations :
  * Reformattage du listing contratplus
  * Les contrats fournisseurs ne sont plus visibles dans le listing quand ils ne sont plus activés

---

# ChangeLog pour [v.12.0.0]
* Améliorations :
  * Compatibilité Dolibarr 10 11 & 12 mise à jour

---

# ChangeLog pour [v.10.0.3]
* Ajouts :
  * Ajout de la dépendance avec le module H2G2
  * Mise en place du système de prélèvement sepa

* Améliorations :
  * Modification de la fonction mail pour afficher les évènements lors de l'envoi d'une facture par mail
  * Modification des listings pour les rendre dynamique avec les extrafields

---

# ChangeLog pour [v.10.0.2]
* Ajouts :
  * Ajout des variables de substitution pour ref client, note publique et note privée d'un contrat
  * Ajout de nouvelles substitutions :
    * __CURRENT_WEEK__ -> indiquant numéro de la semaine en cours, par ex. Semaine 2, Semaine 52, ...
    * __CURRENT_QUARTER__ -> indiquant le numéro du trimestre en cours, par ex. 1er trimestre, 3ième trimestre,
    * __CURRENT_HALF__ -> indiquant le numéro du semestre en cours, par ex. 1er semestre, 2nd semestre,
  * Ajout d'un lien sur un contrat pour afficher les substitutions disponibles
  * Ajout d'un lien sur le menu de gauche pour accéder aux nouveautés
  * Ajout de la sélection d'une ligne sur les listing lors du clique sur celle ci
  * Ajout de hook les actions de masse
  * Ajout de hook sur les onglets des stats contrats plus
  * Ajout d'une action de masse sur le listing des services pour assigner une date d'activation et une durée d'engagement

* Améliorations :
  * Modification de traductions
  * Masquage des commerciaux désactivés sur les champs de sélection des commerciaux
  * Modification du système pour intégrer les modèles pdf Contrat Plus à Dolibarr

* Corrections :
  * Affichage du nom des services sur la fenêtre de confirmation de renouvellement si le service est un service libre

---

# ChangeLog pour [v.10.0.1]
* Ajouts :
  * Ajout des traductions (Allemand, Espagnol, Arabe)
  * Ajout d'un système permettant de changer l'ordre des services sur un contrat (onglet contrat plus seulement)
  * Ajout d'un onglet Contrat Plus sur la page d'un contrat
  * Ajout d'un système de "Message of the day" dans les entêtes de certaines pages avec la configuration associé
  * Ajout d'un filtre pour afficher les contrats actif ou inactif
  * Ajout de message de prévention si l'option contrat fournisseur est désactivé mais que des contrats fournisseurs sont créés
  * Ajout d'un modèle PDF concernant la facturation
  * Ajout d'un modèle PDF concernant les contrats
  * Ajout d'un dashboard principal qui affiche des statistiques

* Améliorations :
  * Amélioration des modèles de facture pour qu'ils se calquent sur ceux de Dolibarr 10
  * Amélioration des listings de contrats et services afin qu'ils soient dynamique
  * Modification de certaines traductions
  * Masquage des champs Date prévue mise en service et Date prévue fin de service
  * Amélioration de certains visuels sur les listings et les cards
  * Amélioration des modèles pdf existants avec la configuration associée
 
* Corrections : 
  * Problème avec la gestion des nombres de pages sur le listing des contrats et services
  * Problème de calcul de la TVA sur le listing des services
  * Problème de filtre Tous / Client / Fournisseur sur le listing des contrats
  * Problème avec la taille de certains icônes
  * Problème de réinitialisation des colonnes lié au listing dynamique
  * Problème d'ajout d'extrafields non pris en compte
  * Problème de renouvellement d'un contrat en mode 3 sans aucun moyen de paiement

---

# ChangeLog pour [v.10.0.0]
* Correction problème lié à l'affichage des boutons d'action (renouveler plus, etc)
* Changement des techniques d'inclusion de fichier
* Changement de la version du module

---

# ChangeLog pour [v.2.5.4]
* Les services fermés ne sont plus facturés
* Correction de bugs
* Evolution des modèles de document PDF pour Dolibarr 8.x / 9.x

---

# ChangeLog pour [v.2.5.2]
* Compatibilité Dolibarr 8.x / 9.x
* Le code source est validé selon les critères DOLIBARR (Branches master & developement)
* Génération automatique du fichier d'archive ZIP du module

---

# ChangeLog pour [v.2.5.1]
* Possibilité de choisir un mode de paiement par défaut s'il n'a pas été défini pour un tiers.
* Possibilité de choisir un type de paiement par défaut s'il n'a pas été défini pour un tiers.
* Fix bug de TVA sur le modèle pdf Contrat Plus Customer

---

# ChangeLog pour [v.2.5.0]
* Ajout du renouvellement automatique des contrats via tâches cron
* Ajout d'une page de configuration pour le cron
* Ajout d'un nouveau statut 'clôturé' pour un contrat
* Modification et amélioration du listing des contrats
* Fix bug sur le modèle pdf Contrat Plus Customer

---

# ChangeLog pour [v.2.4.2]
* Fix problème services avec plusieurs tags dupliqués dans le listing services
* Fix problème affichage d'un message d'erreur (Aucun contrat selectionné) pendant une recherche sur le listing contrat

---

# ChangeLog pour [v.2.4.1]
* Ajout de l'action d'envoi de mail dans l'agenda si le module est activé
* Ajout de l'id de l'expéditeur du mail dans l'historique et le fichier de log
* Ajout d'un boutton de test d'envoi de mail dans la page configuration
* Ajout de message pour notifier l'utilisateur de configurer le module à la première installation
* Fix problèmes visuels mineurs sur un dolibarr 7 

---

# ChangeLog pour [v.2.4.0]
* Ajout de l'option numéro 3 qui permet d'envoyer la facture du client par mail
* Ajout d'un icône visuel sur le listing des contrats permettant de voir si le tiers peut recevoir des mails
* Désactivation de la selection multiple pour les contrats ne pouvant pas recevoir de mail en option 3
* Désactivation du bouton de renouvellement sur un contrat si il ne peut pas recevoir de mail en option 3
* Ajout d'un fichier de log pour les mails (htdocs/documents/contratplus)
* Modification des sous onglets de l'onglet Configuration
    * Nouvel onglet Configuration Email : Permet de parametrer l'objet et le corp du mail
    * Nouvel onglet Historique Email : Permet d'afficher l'historique des emails en se basant sur le fichier de log
* Modification des sous onglets de l'onglet Documentation
    * Nouvel onglet Changelog : Affiche le changelog

---

# ChangeLog pour [v.2.3.4]
> Fix major bug

* Fix problème bypass confirmation
* Fix problème listing des services
* Fix problème accentuation modèle pdf
* Fix problème renouvellement des services inactifs
* Mise en relation date de fin d'engagement et date de fin de service

---

# ChangeLog pour [v.2.3.3]
> Fix version 2.3.2

* Amélioration listing
* Modification description du module et nom du module
* Correction orthographique
* Fix problème de traduction
* Fix problème de doublon listing des services 

---

# ChangeLog pour [v.2.3.2]
* Ajout sous total listing des services
* Création des modèles pdf contratplus pour les factures fournisseurs et clients
* Modification de l'id du module
* Masquage des champs fournisseur lors de la désactivation de l'option contrat fournisseur
* Ajout README en anglais

---

# ChangeLog pour [v.2.3.1]
* Gestion des contrats fournisseur
* Ajout d'un nouveau menu
* Ajout de CI/CD
* Compatibilité Php 5.6 / Php7.x
* Compatibilité Dolibarr V4.x / (V7 en dev)

---

# ChangeLog pour [v.2.3]
* Nouveau menu
* Compatibilité Dolibarr V7
* Nouvelle option
* Code optimisé et corrigé

---

# ChangeLog pour [v.2.2]
* Réorganisation du plugins - issue #1

---

# ChangeLog pour [v.2.1]
* Ajout de la traduction avec un fichier langs

---

# ChangeLog pour [v.2.0]
* Ajout de l'option numéro 2 qui permet de valider la facture brouillon
* La note publique de la facture contient maintenant le trimestre de la facture
* Création de la page de selection multiple des contrats à renouveler
* Renouvellement de plusieurs contrats en même temps choisis via la page de selection du menu de Contrat Plus
* Création d'un récapitulatif de tous les services de chaque contrat à renouveller lors d'une selection multiple
* Ajout d'un sous menu "Tout renouveler" pour accéder à la page de renouvellement multiple plus facilement
* Modification de la page de configuration:
  . Ajout de l'option 2 et 3 selectionable dans la page de configuration
  . Ajout d'un bouton permettant d'accéder à la page de renouvellement multiple
  . Ajout de la configuration d'envoi de mail qui est obligatoire lorsque l'option 3 est activée

---

# ChangeLog pour [v.1.0]
* Ajout d'un bouton permettant l'automatisation de la clôture de tous les services expirés et de la recréation de ceux-ci pour la période suivante
* Ajout d'une option permettant la création d'un brouillon de la facture automatiquement après le renouvellement des services expirés
* Création d'une page de configuration
* Création d'un menu dans la barre de menu du haut permettant un accès plus facile à la page de configuration et à liste des contrats
* Création d'une option permettant de bypass la validation de renouvellement du ou des contrats
* Intégration de la log des requêtes SQL
* Création de la catégorie "Code 42" dans la liste des modules et l'ajout du module dans celle-ci
