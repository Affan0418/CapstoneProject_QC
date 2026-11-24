<h1>Dashboard Region</h1>
<p>Halo, {{ auth()->user()->name }}</p>
<form method="POST" action="{{ route('logout') }}">@csrf<button>Logout</button></form>