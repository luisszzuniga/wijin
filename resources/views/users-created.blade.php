<h3>Bienvenue sur Wijin !</h3>
<p>Voici vos identifiants de connexion :</p>

<br />
<p>Email : {{ $user->email  }}</p>
<p>Mot de passe : {{ $password }}</p>

<br />
<a href="{{ env('APP_URL') }}/admin/login" class="underline">Me connecter</a>
