<?php
$mail = "michelageou@gmail.com";
$mdp = "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
$headers = array(
  'From' => 'mdp@odaame.org',
  'X-Mailer' => 'PHP/' . phpversion()
);
mail($mail, "Votre mot de passe", "Bonjour!\n" .
"\n" .
"Vous venez de créer une liste sur mon site web seulement, les tests de sécurité n'étant pas terminés, je vous ai créé un mot de passe expréssément pour l'occasion.\n" .
"Je n'y ai bien sûr pas accès ni personne en soit et bien sûr je ne garde qu'un hash mais on sait jamais on sait jamais...\n" .
"Je vous invite aussi à régulièrement sauvegarder votre liste à l'aide de la wayback machine (https://archive.org/web/) ou de screen shots d'autant plus que le site n'étant pas du tout terminé, nous ne sommes pas à l'abri d'une mise à jour destructive (Auquel ca vous seriez prévenus par mail mais pour peu que ça finisse dans vos spams...).\n" .
"\n" .
"Votre mot de passe est donc ".$mdp.". \n" .
"\n" .
"Cordialement,\n" .
"\n" .
"reza0310.", $headers);
?>
