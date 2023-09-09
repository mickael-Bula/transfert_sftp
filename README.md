# Utilisation du serveur sftp Rebex Tiny SFTP Server

Permet de tester simplement et rapidement une connexion SFTP.
Il suffit de télécharger l'exécutable et de décompresser le zip pour bénéficier du serveur.
Le téléchargement se fait ici : https://www.rebex.net/doc/tiny-sftp-server/

# Configuration

Le serveur se trouve dans le répertoire `C:\Users\bulam\RebexTinyServer`.

Pour commencer, il faut activer l'utilisation de la connexion par clé.
Pour cela, deux étapes :

- configurer le répertoire contenant les clés publiques utilisées :

```xml
    <add key="userPublicKeyDir" value="C:\Users\bulam\.ssh" />
```

- ensuite, déclarer le fichier contenant la clé privée :

```xml
    <add key="rsaPrivateKeyFile" value="C:\Users\bulam\.ssh\id_rsa" />
```

>NOTE : Il faut veiller à l'adresse IP du serveur SFTP qui est susceptible de changer à chaque nouvelle connexion.

La connexion SFTP par user et mot de passe ne pose pas de problème particulier, si ce n'est la vérification du port.
En effet, si le port par défaut d'une connexion SFTP est 22, il est à noter que Rebex utilise le 2222.
Il s'agit donc de configurer correctement ce port dans le code :

```xml
<add key="sshPort" value="2222" />
```

En revanche, pour la connexion par clé, j'ai rencontré quelques difficultés mal documentées.
Pour établir la connexion, j'ai créé une paire de clés au format Open-ssh comme indiqué : 

```cmd
$ ssh_keygen -t rsa
```

Cependant, après avoir enregistré les clés dans le répertoire .ssh de mon utilisateur et avoir déclaré la clé publique dans le xml de RebexTinySftpServer.exe.config, la clé n'était pas reconnue.

La solution consiste à convertir cette clé générée au format ssh2 : 

```bash
$ cd C:\Users\bulam\.ssh
$ ssh-keygen.exe -f id_rsa.pub -e -m RFC4716 > id_rsa.pub
```

## Répertoire de transfert

Le dossier utilisé pour les transferts depuis le serveur est configuré dans le xml comme ceci :

```xml
<add key="userRootDir" value="data" />
```

## Répertoire cible

Afin que le fichier récupéré sur le serveur distant puisse être enregistré localement, il faut que le répertoire cible ait été préalablement créé.
Dans le cas contraire, une erreur `fopen()` est générée.

## Lancement du script

```bash
$ php transfert_sftp.php
```

## Troubleshooting

Après une perte de connexion réseau, il est nécessaire de relancer le serveur : se contenter de fermer la connexion ne suffit pas.
Il faut alors veiller à l'IP du serveur qui peut avoir changé. Dans ce cas, actualiser celle-ci dans le `.env`.