1 installer les packages :
composer require knplabs/github-api
composer require league/oauth2-github

aller sur github->settings->developer settings->OAuth Apps et créer une apps.

mettre la homepage dans homepage URL et un call-back et on enregistre

On a un client ID et un secret que l'on génere.

dans le fichier env on va créer 3variables:
GITHUB_ID={client ID}
GITHUB_SECRET={secret ID}
GITHUB_CALLBACK=