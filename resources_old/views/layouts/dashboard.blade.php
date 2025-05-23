<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('dashboard_title') - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Option 1: Include in HTML -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">
</head>
<style>
    @media (min-width: 800px) {
        .sidebar {
            height: 100vh;
            position: fixed;
        }
    }
</style>
<body>
<div class="row">
    <div class="col-md-3 col-12">
        <div class="d-flex flex-column p-3 text-white bg-dark sidebar">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4">Dashboard</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{route('dashboard.index')}}"
                       class="nav-link text-white {{ Route::currentRouteNamed('dashboard.index') ? 'active' : '' }}"
                       aria-current="page">
                        <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-book"></i> Mon livret d'accueil
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{route('dashboard.edit_livret')}}">Editer</a></li>
                        <li><a class="dropdown-item" href="{{route('dashboard.background')}}">Changer le fond</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item"
                               href="{{route('livret.show',[\Illuminate\Support\Facades\Auth::user()->livret->slug,\Illuminate\Support\Facades\Auth::user()->livret->id])}}">Voir</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{route('dashboard.stats')}}" class="nav-link text-white {{ Route::currentRouteNamed('dashboard.stats') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i> Statistiques
                    </a>
                </li>
                <li>
                    <a href="{{route('dashboard.inventories')}}"
                       class="nav-link text-white {{ Route::currentRouteNamed('dashboard.inventories') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> Etats de lieux
                    </a>
                </li>
                <li>
                    <a href="{{route('dashboard.suggestions')}}"
                       class="nav-link text-white {{ Route::currentRouteNamed('dashboard.suggestions') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i> Mes suggestions
                    </a>
                </li>
                <li>
                    <a href="{{route('dashboard.profile')}}"
                       class="nav-link text-white {{ Route::currentRouteNamed('dashboard.profile') ? 'active' : '' }}">
                        <i class="bi bi-person"></i> Mon profil
                    </a>
                </li>
                <li>
                    <a href="{{route('dashboard.products')}}"
                       class="nav-link text-white {{ Route::currentRouteNamed('dashboard.products') ? 'active' : '' }}">
                        <i class="bi bi-shop"></i> Notre boutique
                    </a>
                </li>
                <li>
                    <a class="nav-link text-white" href="{{route('dashboard.logout')}}">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </a>
                </li>
            </ul>
            <hr>
            <ul>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#contactModal">
                    Nous contacter
                </button>
            </ul>
            <ul>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#shareModal">
                    Partager
                </button>
            </ul>
        </div>
    </div>

    <div class="col-md-9 col-10">
        <main>
            @yield('dashboard_content')
        </main>
    </div>
</div>
</body>
</html>
