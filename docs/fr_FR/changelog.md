# Changelog plugin Watchdog

>**IMPORTANT**
>
>S'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

**BETA**

*   Refonte de la documentation. Réorientation de la documentation et du changelog Béta vers la branche beta de Github.
*   Refonte du code afin d'améliorer la compréhension et de faciliter la maintenance et les évolutions ultérieures
*   Développement sous Debian 12: 
*       pas de problème rencontré avec le lancement des actions
*       le message d'erreur mod_insert ... n'apparait plus (pour info, il apparaissait également de manière aléatoire en Debian 11)
*	Version minimale de Jeedom 4.4
*   Ajout de la commande Refresh (créée lors de la sauvegarde de l'équipement). On peut lancer un Refresh en cliquant sur l'icone en haut à droite de la tuile.
*   Ajout dans la configuration du plugin et au niveau de chaque équipement d'un paramètre indiquant si le contrôle est OK lorsque le résultat global est à true ou à false. Cela permet d'indiquer la crosse verte si le résultat des contrôles est False
*   Reporting des watchdogs dans un virtuel
*   Possibilité de spécifier le widget à utiliser pour les conditions et le résultat global
*   Gestion de l'historique pour les conditions et le résultat global
*   Gestion de macro permettant de répéter la même condition en faisant varier les paramètres, équipement en particulier
*   Ajout de variables pour les équipements pour utilisation dans les conditions et actions
*   Dans les actions, possibilité de récupérer l'équipement ou la commande à l'origine du lancement de l'action (dans le mode Actions sur chaque contrôle)
*   Ajout des actions après, possibilité d'appliquer les actions Avant/Après sur chaque controle

**Version 2025-06-10**

*   Reprise de la gestion du plugin par @bernard.dandrea (un grand merci à @sigalou pour le formidable travail effectué sur le plugin)
*   Application du PR de TommyChausson concernant la suppression des php notices récurrents "only variables should be passed by reference"
*   Application du PR de TomsnCo concernant la gestion de l'arborescence
*   Transfert de la documentation et du changelog du site de @sigalou vers github
*   Modification de info.json pour refléter les changements de propriétaire et les liens de la documentation
*   Blocage de la suppression du champ 'Resultat Global' qui se produisait lors de la sauvegarde
*   Suppression du répertoire core/template qui ne sert à rien
*   Rename de la procédure toHTML dans watchdog.class.php car elle provoque l'affichage de #cmd# dans les tuiles au lieu de 'Resultat global'

**Version 2021-03-20 01:09:51**

*   Refonte du tableau des « contrôles à effectuer », suppression du fond, grossissement de l’icône Remove tout à droite et passage en rouge.
*   Activation de la 3ème case des actions à exécuter, pour qu’une info soit noter dans le log au moment de l’execution
*   Ajout d’un log spécifique par Watchdog, une case à cocher dans le device pour l’activer
*   Grosse modif pour ajouter une nouvelle commande info : **\[Résultat Global\]**
*   Ajout de l’affichage du résultat global (en cas de mode Et ou OU)

**Version 2020-05-01**

*   Grosse modif du calcul global du mode ET
*   Simplification des logs

**Version 2020-04-21**

*   Modification de la police des logs pour avoir du monospace
*   Ajout d’un nouveau mode « Mode de fonctionnement des actions »

**Version 2020-04-19**

*   Corrections de petits bugs
*   Ajout d’un bouton **Tester**
*   Refonte complète des logs
*   Contrôle du fonctionnement du mode avec bilan global Et ou OU

**Version 2020-04-11 17:35  
**

*   Ajout d’un nouveau contrôle : **Surveiller la config IP de Jeedom**
*   Corrections des designs et des soucis de couleurs
*   Corrections de petits bugs
*   Corrections des boutons qui affichaient true/false/? qui n’était plus en blanc

**Version 2019-10-31 20:12:13**

*   Bouton permettant d’afficher (et masquer) les calculs des contrôles
*   Ajout d’un log par Watchdog, alimenté par les variables title et message des commandes.
*   Remplacement de **#name# par #controlname#** pour être compatible V4
*   Modification de l’affichage de la liste Objet parent (jeeObject) pour la rendre compatible V4
*   Refonte des couleurs à cause du css de la V4

**Version 2019-05-30 17:52:31  
**

*   Ajout de la possibilité de contrôler un équipement (pour vérifier le délai depuis la dernière communication avec lui par exemple)
*   Ajout de la possibilité d’éxecuter une (ou des) action(s) avant de lancer un contrôle (pour faire un refresh par exemple)
*   Divers améliorations graphiques.

