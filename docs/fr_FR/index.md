<!--  
  Last Modified : 2025/09/14 15:19:03
-->
- [Plugin Watchdog](#plugin-watchdog)
- [Installer le Plugin Watchdog](#installer-le-plugin-watchdog)
- [Configurer le plugin](#configurer-le-plugin)
- [Configurer le Watchdog](#configurer-le-watchdog)
  - [Onglet Watchdog](#onglet-watchdog)
  - [Onglet Contrôles](#onglet-contrôles)
  - [Onglet Actions](#onglet-actions)
- [Widgets](#widgets)
- [Exemples d’utilisation des Watchdogs](#exemples-dutilisation-des-watchdogs)
  - [Contrôler les équipements du réseau local](#contrôler-les-équipements-du-réseau-local)
    - [Onglet Watchdog](#onglet-watchdog-1)
    - [Onglet Contrôles](#onglet-contrôles-1)
    - [Onglet Actions](#onglet-actions-1)
  - [Relancer le routeur Zigbee si il ne répond pas](#relancer-le-routeur-zigbee-si-il-ne-répond-pas)
    - [Onglet Watchdog](#onglet-watchdog-2)
    - [Onglet Contrôles](#onglet-contrôles-2)
    - [Onglet Actions](#onglet-actions-2)
  - [Contrôler que la sécurité du plancher chauffant ne s'est pas déclenchée](#contrôler-que-la-sécurité-du-plancher-chauffant-ne-sest-pas-déclenchée)
    - [Onglet Watchdog](#onglet-watchdog-3)
    - [Onglet Contrôles](#onglet-contrôles-3)
    - [Onglet Actions](#onglet-actions-3)
- [Avis](#avis)

# Plugin Watchdog

La fonction principale du plugin watchdog est de surveiller les équipements et d'avertir l'administrateur des éventuels problèmes. L'utilisation généralisée du plugin permet de fiabiliser son installation Jeedom et de se montrer proactif dans la détection et résolution des problèmes courants.

Exemples typiques d'utilisation :

*   **Contrôler que les capteurs sont bien actifs**
*   **S’assurer que les équipements réseau sont bien connectés au routeur**
*   **Relancer un équipement qui ne répond plus**
*   **Vérifier que les nuts sont toujours actifs**  

Les contrôles peuvent être programmés sur le modèle de planification des crons.

En fonction du résultat des contrôles, des actions peuvent être lancées, typiquement envoi de messages / mails ou relance d'équipements.

Le résultat des contrôles peut être centralisé dans un équipement virtuel afin d'avoir une vision immédiate des éventuels problèmes.

Le plugin reprend une grosse partie des fonctions trouvées dans les scénarios au niveau des contrôles et des actions. Il se montre bien plus facile à mettre en oeuvre que les scénarios.

Le plugin est autodocumenté: les explications sont fournies au niveau de la configuration du plugin et des watchdogs. Dans cette documentation, on ne reprend pas forcément toutes les explications fournies dans l'interface utilisateur.  

# Installer le Plugin Watchdog

Aller dans le Market Jeedom, trouver le plugin watchdog et installer la version **stable**. Puis **Activer le plugin**.

![014](../images/014.png)

Le plugin est accessible via le menu.

# Configurer le plugin

Dans la configuration, vous pouvez paramétrer les paramètres habituels des plugins et les valeurs par défaut qui seront utilisées par les watchdogs.

![015](../images/015.png)

Cette section définit le paramètrage par défaut pour le résultat des contrôles (expliqué par la suite).

![016](../images/016.png)

Cette section définit le paramètrage par défaut pour l'enregistrement du résultat des conditions dans un virtuel (expliqué par la suite).

Comme pour tout plugin, vous pouvez activer le mode debug pour suivre le détail des contrôles et actions.

# Configurer le Watchdog

La configuration d'un watchdog passe par 3 onglets:

* Watchdog: onglet général où sont définies les caractéristiques et le fonctionnement du watchdog
* Contrôles: onglet où sont définis les tests à effectuer
* Actions: onglet où sont définies les actions à mener en fonction du résultat des tests

![053](../images/053.png)

En haut à droite, vous trouver les actions habituelles de de la configurations des équipements. Il y a 3 boutons supplémentaires:

* un bouton pour accéder à la log du watchdog (log spécifique ou générale)
* un bouton pour cacher/afficher les explications fournies dans les zones en bleu
* un bouton pour accéder à la documentation

Noter que le bouton de Sauver / Contrôler lance les contrôles et permet de détecter les éventuelles erreurs.

## Onglet Watchdog

![017](../images/017.png)

On trouve les champs habituels des équipements Jeedom (nom, objet, ...)..

Il est possible de spécifier une log spécifique pour le watchdog ce qui permet un suivi plus facile de son activité. En cliquant sur le bouton log en haut de l'écran, vous pouvez accéder directement à la visualisation de la log.

La fréquence de lancement du watchdog est définie dans le paramètrage du cron et peut-être déterminée à l'aide de l'assistant.

Les dates des deux derniers lancements et leur mode de lancement sont affichés.

![018](../images/018.png)

Le mode de fonctionnement des contrôles est un paramètre important car il détermine comment le watchdog va se comporter vis à vis des contrôles et des actions.

Trois modes sont possibles:

*   Actions sur chaque contrôle indépendamment : ce mode teste indépendamment chaque contrôle et déclenche les actions quand ce contrôle a changé d’état
*   Actions sur l’ensemble des contrôles (avec méthode OU) : Ce mode exécute tous les contrôles et détermine le résultat global en appliquant la condition OU (le résultat global est True si au moins l'une des conditions vaut True). Il déclenche les actions quand le résultat global a changé d’état.
*   Actions sur l’ensemble des contrôles (avec méthode ET) : Ce mode est le même que le précédent mais en appliquant la condition ET (le résultat global est vrai si toutes les conditions sont égales à True).

Avec les améliorations qui ont été apportées dans le paramétrage des actions, le premier mode doit permettre de traiter la gande majorité des situations.

![019](../images/019.png)

Le mode de fonctionnement des actions détermine dans quel cas les actions sont lancées.

Quatre modes sont possibles. Dans chacun des cas, ce sont les actions correspondant au résultat du contrôle ou du résultat global (True ou False) qui sont lancées.

![045](../images/045.png)

Ce paramètre définit si les actions Avant/Après sont lancées une seule fois (mode par défaut) ou pour chaque contrôle. Cette dernière option n'est valable que dans le mode Actions sur chaque contrôle indépendamment.

![020](../images/020.png)

Le premier paramètre détermine dans quel cas le résultat est considéré comme correct. Cela permet d'afficher des icones significatives dans la tuile et le reporting. Noter qu'avec les conditions générées par le générateur d'expression, un résultat correct est représenté par False. Par défaut, les paramètres sont repris automatiquement de la configuration du plugin. 

![021](../images/021.png)

Cette section fournit le paramètrage pour l'enregistrement des résultat dans un virtuel (voir la section Widget). Par défaut, les paramètres sont repris automatiquement de la configuration du plugin. 

## Onglet Contrôles

Dans cet onlet sont définis les contrôles à effectuer et leur paramétrage.

![022](../images/022.png)

On ajoute les contrôles à effectuer. Ceux-ci sont exprimés de la même façon que dans la condition SI des scénarios. 

Les boutons à droite de l'expression permettent d'appeler le générateur d'expression, de transformer l'expression en macro ou d'appeler le testeur d'expression.

Les contrôles sont lancés en mode programmé (mode CRON), par la commande Refresh (en haut à droite de la tuile) ou lorsque l'on clique sur Sauver / Contrôler (dans ce dernier cas, les actions ne sont pas lancées).

Suivant le paramètrage du watchdog, le résultat des contrôles est affiché dans la tuile du watchdog et/ou dans le virtuel utilisé pour le reporting. Les résultats peuvent être utilisés partout dans Jeedom.

![023](../images/023.png)

Le contrôle ci-dessus a été généré avec le générateur d'expression. La condition est évaluée en cliquant sur "Sauver / Contrôler". Le résultat est affiché. 

![024](../images/024.png)

En cliquant sur le bouton Macro, une macro est générée et l'expression est modifiée pour utiliser la macro. Lorsque la macro est modifiée, il faut cliquer sur Sauver / Contrôler pour qu'elle soit prise en compte dans les expressions.

![025](../images/025.png)

L'utilisation de variables permet des faire varier certains paramètres sans modifier l'expression ou la macro. Lorsqu'une variable est modifiée, il faut cliquer sur Sauver / Contrôler pour qu'elle soit prise en compte dans les expressions.

![026](../images/026.png)

Ici, la macro a été modifiée pour pour passer le délai en paramètre et l'expression la variable tempo1 en deuxième argument.

Noter le test `eqEnable(_arg1_) < 0` qui permet de générer une condition True si l'équipement passé en paramètre a été supprimé.

![044](../images/044.png)

En cas d'erreur, il est possible d'appeler le testeur d'expression qui sera prérempli avec la condition. Ici, il y a une parenthèse fermante en trop.

![027](../images/027.png)

Une fois que l'on est satisfait de l'expression et de la macro, on peut l'appliquer à d'autres équipements. 

![028](../images/028.png)

Dans le cas du mode de fonctionnement OU/ET, le résultat global des tests apparait comme dernière condition. C'est la valeur de cette condition qui va déterminer le lancement des actions.

## Onglet Actions

![029](../images/029.png)

Il existe 4 sortes d'action.

*   Les actions qui sont lancées **AVANT** l'ensemble des contrôles ou chaque contrôle 
*   Les actions qui sont lancées quand le contrôle passe ou est égal à **TRUE**
*   Les actions qui sont lancées quand le contrôle passe ou est égal à **FALSE**
*   Les actions qui sont lancées **APRES** l'ensemble des contrôles ou chaque contrôle 

Le mode de lancement dépend du paramètrage définit dans l'onglet watchdog. Les libellés des actions disent explicitement comment les actions seront lancées.

Les actions peuvent être désactivées, testées et par défaut elles sont enregistrées dans la log watchdog_actions. Noter que les actions s'exécutent en mode séquentiel (à moins que l'on ait coché la case mode parallèle), ce qui fait que l'exécution d'une action peut bloquer le déroulement de l'ensemble des watchdogs. Pour les actions True/False lancées tant que le résultat du contrôle vaut True/False, il est possible de demander à ce qu'une action ne soit effectuées qu'une seule fois (voir exemple [Relancer le routeur Zigbee si il ne répond pas](#relancer-le-routeur-zigbee-si-il-ne-répond-pas) ).


Les actions se définissent de la même façon que dans les scénarios. Il est possible d'utiliser les variables dans les commandes, en particulier pour fournir des informations concernant la raison du problème. Toutes les variables définies dans l'onglet Contrôles sont utilisables.

En mode "Actions sur chaque contrôle indépendamment", la variable \_controlname\_ (ou #controlname#) indique quel est le contrôle à l'origine de l'action. de plus, les variables \_equip\_ (\_equipname\_ pour le nom) et \_cmd\_ (\_cmdname\_) sont fournies, elles indiquent le premier équipemnt / commande qui apparait dans l'expression de la condition à l'origine de l'action. Cela permet par exemple d'envoyer des messages plus précis sur l'origine de l'erreur. 

![041](../images/041.png)

Dans l'exemple ci-dessus un mail et un message sont envoyés.

![043](../images/043.png)

Le message utilise l'expression `_controlname_ n'est plus en ligne: dernière communication avec _equipname_ :  lastCommunication(_equip_)`.

# Widgets

![030](../images/030.png)

La tuile du watchdog permet de voir immédiatement l'état des équipements contrôlés. On peut choisir de voir l'état de tous les contrôles (valeur par défaut) ou seulement ceux en erreur.

En cliquant sur le bouton en haut à droite de la tuile, on lance la commande Refresh.

![031](../images/031.png)

La tuile du virtuel utilisé pour le reporting permet de voir immédiatement l'état des équipements des watchdogs qui lui sont ratachés. On peut choisir de voir l'état de tous les contrôles ou seulement ceux en erreur (valeur par défaut).

Dans les 2 cas, les templates sont repris des paramètres du watchdog. Si l'historique est activé, on peut le consulter en cliquant sur le contrôle.

![054](../images/054.png)

Noter que dans la partie gestion du plugin, on peut lancer la suppression dans le virtuel des résultats orphelins et renommer les résultats en fonction du nom des watchdogs et contrôles.

# Exemples d’utilisation des Watchdogs 

## Contrôler les équipements du réseau local

Pour cet exemple, on va s’appuyer sur le plugin **Network**. Dans notre cas, il est configuré pour émettre un ping sur l'équipement concerné toutes les minutes.

Si l'équipement ne répond pas, la commande info [Statut] va passer à zéro.

Afin de ne pas générer d'alerte si on l'équipement est en cours de redémarrage, on testera que le statut est à zéro depuis au moins 5 minutes.

### Onglet Watchdog

![033](../images/033.png)

Dans cette configuration, le watchdog est lancé toutes les 5 minutes et le mode de fonctionnement est "Action sur chaque contrôle indépendamment".

### Onglet Contrôles

![034](../images/034.png)

La macro est la suivante: `(#_arg1_[Statut]# == 0 ET (#timestamp#-strtotime(valuedate(#_arg1_[Statut]#)) > _tempo1_)  ET eqEnable(_arg1_)==1 ) or eqEnable(_arg1_) < 0`

Elle teste que le statut est à zéro depuis tempo1, que l'équipement Network est actif et défini.

![037](../images/037.png)

La macro est appliquée aux équipement à contrôler.

![036](../images/036.png)

La condition générée peut être évaluée.

### Onglet Actions

![038](../images/038.png)

Un mail et un message sont envoyés lorsque l'on perd la connexion.

![039](../images/039.png)

Expression de l'email: `Dernière communication avec _equipname_ :    valuedate(_cmd_)`

![040](../images/040.png)

Expression du message : `#controlname# n'est plus en ligne: dernière communication avec _equipname_ :   valuedate(_cmd_)`


## Relancer le routeur Zigbee si il ne répond pas

Dans mon installation, il est arrivé que les routeurs Zigbee cessent de fonctionner (problème heureusement résolu depuis par un changement de firmware). Le seul moyen de les remettre en ligne était de les éteindre et de les redémarrer électriquement.

Pour traiter ce cas, j'ai branché les routeurs sur un switch Zigbee. Le but du watchdog ci-dessous est de lancer une commande de rafraichissement et de passer le switch en on/off si on perd la connexion. Un mail est envoyé (une seule fois) lorsque l'on constate la déconnexion.

### Onglet Watchdog

![046](../images/046.png)

Le watchdog est lancé toutes les 5 minutes.

![047](../images/047.png)

Les actions sont lancées sur chaque contrôle indépendamment.

Les actions correspondant à True sont lancées aussi longtemps que le contrôle est en erreur.

Les actions Avant sont lancées avant chaque contrôle.

### Onglet Contrôles

![048](../images/048.png)

La macro est la suivante: `((#timestamp# - strtotime(lastCommunication(_arg1_))) > _tempo1_  et eqEnable(_arg1_) == 1) ou eqEnable(_arg1_) < 0`

Elle teste que la dernière communication date de moins de 10 minutes.

Les macros sont appliquées à 3 routeurs zigbee. Le deuxième argument indique quel est le switch sur lequel est connecté le routeur. Il n'est pas utilisé dans le contrôle mais servira dans les actions.

### Onglet Actions

![049](../images/049.png)

L'action avant est destinée à maintenir la communication avec le routeur. L'équipement correspondant est déterminé par la variable `_arg1_`.

![050](../images/050.png)

En standard, le routeur zigbee ne propose pas de commande permettant de le réveiller. Aussi une commande permettant de fixer le niveau d'émission a été détournée de son usage primaire. La commande KeepAlive force la communication avec le routeur. Elle est déclenchée à la même fréquence que le watchdog soit toutes les 5 minutes.

![051](../images/051.png)

Le résultat du contrôle passe à True si il n'y a pas de communication depuis plus de 10 minutes (comme le routeur est réveillé toutes les 5 minutes, cela correspond à une anomalie).

Les actions permettent d'envoyer un mail et un message et de relancer le routeur en coupant et rétablissant son alimentation.

Noter la coche sur la l'envoi de mail qui permet d'envoyer le mail seulement une fois.

![052](../images/052.png)

Quand la situation est rétablie, un mail et un message sont envoyés.

## Contrôler que la sécurité du plancher chauffant ne s'est pas déclenchée

Le plancher chauffant est protégé des surchauffes par un thermostat de sécurité qui coupe la circulation d'eau chaude si la température de départ devient trop élevée. En général, on se rend compte du problème à partir du moment où la baisse de température dans les pièces devient sensible, ce qui n'est pas agréable.

Afin d'éviter de désagrément, un watchdog détecte cette situation en vérifiant que la température de retour est bien inférieure à la température de départ. Dans ce cas, un mail est envoyé afin de demander la vérification de la sécurité.

Cet exemple illustre le mode de fonctionnement avec la méthode ET.

### Onglet Watchdog

![055](../images/055.png)

Dans cette configuration, le watchdog est lancé toutes les 5 minutes et le mode de fonctionnement est "Action sur l'ensemble des contrôle avec la méthode ET". Les actions sont lancées sur le changement d'état du résultat global.

### Onglet Contrôles

![056](../images/056.png)

Il y a 4 conditions distinctes:

* les deux premières vérifient que les capteurs de températures départ et retour sont mises à jour régulièrement
* la troisième vérifie que la pompe qui fait circuler l'eau dans le plancher est active
* la quatrière contrôle que sur les 5 dernières minutes la température de retour est supérieure à celle de départ d'au moins 5 degrés

Si l'ensemble de ces conditions est à True, le résultat global passe à True.

### Onglet Actions

![057](../images/057.png)

Un mail est envoyé lorsqu'il y a une anomalie et lors du retour à la normale.

# Avis

**Si vous appréciez ce plugin, merci de laisser une évaluation et un commentaire sur le Jeedom market, ça fait toujours plaisir:** <https://jeedom.com/market/index.php?v=d&p=market_display&id=3716#>

![avis](../images/032.png)