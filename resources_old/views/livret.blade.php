<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$livret->livret_name}} | Livret d'accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
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
    <style>
        .website-icon {
            color: #000000;
            font-size: 1rem;
        }

        .instagram-icon {
            color: #C13584;
            font-size: 1rem;
        }

        .facebook-icon {
            color: #3b5998;
            font-size: 1rem;
        }

        .linkedin-icon {
            color: #0077b5;
            font-size: 1rem;
        }

        .twitter-icon {
            color: #1DA1F2;
            font-size: 1rem;
        }

        .tripadvisor-icon {
            color: #34E0A1;
            font-size: 1rem;
        }

        .socials {
            padding: 0;
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .socials div {
            width: max-content;
        }

        .card {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            transition: 0.3s;
            height: 150px;
            width: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .card:hover {
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        .card h2 {
            font-size: 1rem;
        }

        .card i {
            font-size: 3rem;
        }
        @if($livret->font)
        body {
            font-family: {{$livret->font}}, sans-serif;
        }
        @endif
        @if($livret->text_color)
        body {
            color: {{$livret->text_color}};
        }
        @endif
    </style>

</head>
<body
    style="min-height:100vh;background-image: url({{asset($livret->background)}});background-repeat: no-repeat;background-size: cover">
<main>
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <img src="{{ asset($livret->logo) }}" alt="Logo" style="width: 100px;"
                     class="img-fluid rounded-circle mx-auto d-block">
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-lg-6">
                <h1 class="display-4">{{ $livret->livret_name }}</h1>
                <p class="lead">{!! nl2br(e($livret->description)) !!}</p>
            </div>
        </div>
        @if(session('success_suggest'))
            <div class="alert alert-success mt-4" role="alert">
                {{ session('success_suggest') }}
            </div>
        @endif
        @if(session('error_suggest'))
            <div class="alert alert-danger mt-4" role="alert">
                {{ session('error_suggest') }}
            </div>
        @endif
        <div class="row">
            @foreach($livret->wifi->unique('order')->concat($livret->digicode->unique('order'))->concat($livret->endInfos->unique('order'))->concat($livret->utilsPhone->unique('order'))->concat($livret->startInfos->unique('order'))->concat($livret->utilsInfos->unique('order'))->concat($livret->NearbyPlaces->unique('order'))->sortBy('order') as $item)
                @if($item instanceof App\Models\ModuleWifi)
                    @if($livret->wifi)
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <button type="button" class="btn w-100" data-bs-toggle="modal" data-bs-target="#wifiModal">
                                <div class="card text-center w-100">
                                    <i class="bi bi-wifi"></i>
                                    Wifi
                                </div>
                            </button>
                        </div>
                        <div class="modal fade" id="wifiModal" tabindex="-1" aria-labelledby="wifiModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="wifiModalLabel">WiFi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Pour vous connecter à notre réseau Wifi, veuillez accéder aux paramètres Wifi de votre appareil</p>
                                        @if(!$livret->wifi->isEmpty())
                                            <table class="table table-striped mt-3">
                                                <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Code</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($livret->wifi as $item)
                                                    <tr>
                                                        <td>{{$item->ssid}}</td>
                                                        <td>{{$item->password}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif
                @elseif($item instanceof App\Models\ModuleDigicode)
                    @if(!$livret->digicode->isEmpty())
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <button type="button" class="btn w-100" data-bs-toggle="modal"
                                    data-bs-target="#digicodeModal">
                                <div class="card text-center w-100">
                                    <i class="bi bi-key-fill"></i>
                                    Digicode
                                </div>
                            </button>
                        </div>
                        <div class="modal fade" id="digicodeModal" tabindex="-1" aria-labelledby="digicodeModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="digicodeModalLabel">Digicode</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Voici les digicodes dont vous aurez besoin pendant votre séjour !</p>
                                        @if(!$livret->digicode->isEmpty())
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Code</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($livret->digicode as $item)
                                                    <tr>
                                                        <td>{{$item->name}}</td>
                                                        <td>{{$item->code}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @elseif($item instanceof App\Models\ModuleEndInfos)
                    @if(!$livret->endInfos->isEmpty())
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <button type="button" class="btn w-100" data-bs-toggle="modal"
                                    data-bs-target="#endInfosModal">
                                <div class="card text-center w-100">
                                    <i class="bi bi-info-circle-fill"></i>
                                    Infos départ
                                </div>
                            </button>
                        </div>
                        <div class="modal fade" id="endInfosModal" tabindex="-1" aria-labelledby="endInfosModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="endInfosModalLabel">Infos de départ</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Vous trouverez ici toutes les informations concernant le départ de notre établissement</p>
                                        @if(!$livret->endInfos->isEmpty())
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Infos</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($livret->endInfos as $item)
                                                    <tr>
                                                        <td>{{$item->name}}</td>
                                                        <td>{{$item->text}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif
                @elseif($item instanceof App\Models\ModuleUtilsPhone)
                    @if(!$livret->utilsPhone->isEmpty())
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <button type="button" class="btn w-100" data-bs-toggle="modal"
                                    data-bs-target="#utilsPhoneModal">
                                <div class="card text-center w-100">
                                    <i class="bi bi-telephone-fill"></i>
                                    Numéros utile
                                </div>
                            </button>
                        </div>
                        <div class="modal fade" id="utilsPhoneModal" tabindex="-1" aria-labelledby="utilsPhoneModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="utilsPhoneModalLabel">Numéros utiles</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Vous trouverez ici toutes les numéros dont vous pouvez avoir besoin</p>
                                        @if(!$livret->utilsPhone->isEmpty())
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Numéro</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td>Police</td>
                                                    <td><a href="tel:17">17</a></td>
                                                </tr>
                                                <tr>
                                                    <td>Pompiers</td>
                                                    <td><a href="tel:18">18</a></td>
                                                </tr>
                                                <tr>
                                                    <td>Samu</td>
                                                    <td><a href="tel:15">15</a></td>
                                                </tr>
                                                <tr>
                                                    <td>Urgences</td>
                                                    <td><a href="tel:112">112</a></td>
                                                </tr>
                                                @foreach($livret->utilsPhone as $item)
                                                    <tr>
                                                        <td>{{$item->name}}</td>
                                                        <td><a href="tel:{{$item->number}}">{{$item->number}}</a></td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif
                @elseif($item instanceof App\Models\ModuleStartInfos)
                    @if(!$livret->startInfos->isEmpty())
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <button type="button" class="btn w-100" data-bs-toggle="modal"
                                    data-bs-target="#startInfosModal">
                                <div class="card text-center w-100">
                                    <i class="bi bi-info-circle-fill"></i>
                                    Infos arrivée
                                </div>
                            </button>
                        </div>
                        <div class="modal fade" id="startInfosModal" tabindex="-1" aria-labelledby="startInfosModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="startInfosModalLabel">Infos d'arrivée</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Vous trouverez ici toutes les informations concernant votre arrivée dans notre établissement</p>
                                        @if(!$livret->startInfos->isEmpty())
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Infos</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($livret->startInfos as $item)
                                                    <tr>
                                                        <td>{{$item->name}}</td>
                                                        <td>{{$item->text}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif
                @elseif($item instanceof \App\Models\ModuleUtilsInfos)
                    @if(!$livret->utilsInfos->isEmpty())
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <button type="button" class="btn w-100" data-bs-toggle="modal"
                                    data-bs-target="#utilsInfosModal">
                                <div class="card text-center w-100">
                                    <i class="bi bi-info-circle-fill"></i>
                                    Infos utiles
                                </div>
                            </button>
                        </div>
                        @include('inc.modules_modals.module_utils_infos')
                    @endif
                @elseif($item instanceof App\Models\NearbyPlace)
                    @if(!$livret->placeGroups->isEmpty() && !$livret->NearbyPlaces->isEmpty())
                        <div class="col-lg-4 col-md-4 col-sm-6">
                            <button type="button" class="btn w-100" data-bs-toggle="modal"
                                    data-bs-target="#placesGroupsModal">
                                <div class="card text-center w-100">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    Lieux à proximité
                                </div>
                            </button>
                        </div>
                        <div class="modal fade" id="utilsInfosModal" tabindex="-1" aria-labelledby="utilsInfosModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="utilsInfosModalLabel">Infos pratiques</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Vous trouverez ici toutes les informations dont vous pouvez avoir besoin</p>
                                        @if(!$livret->utilsInfos->isEmpty())
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Infos</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($livret->utilsInfos as $item)
                                                    <tr>
                                                        <td>{{$item->sub_name}}</td>
                                                        <td>{{$item->text}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif
                @endif
            @endforeach
        </div>
    </div>
</main>
<footer class="container mb-5">
    @if($livret->suggest)
        <div class="row">
            <button type="button" class="btn btn-primary col-3 mx-auto mb-5" data-bs-toggle="modal"
                    data-bs-target="#suggestionModal">
                Envoyé une suggestion
            </button>
        </div>
        @include('inc.add_suggestions_modal')
    @endif
    <div class="row socials">
        @if($livret->establishment_website)
            <div class="col-md-2">
                <a href="{{ $livret->establishment_website }}" target="_blank"><i class="bi bi-globe website-icon"></i></a>
            </div>
        @endif
        @if($livret->instagram)
            <div class="col-md-2">
                <a href="{{ $livret->instagram }}" target="_blank"><i class="bi bi-instagram instagram-icon"></i></a>
            </div>
        @endif
        @if($livret->facebook)
            <div class="col-md-2">
                <a href="{{ $livret->facebook }}" target="_blank"><i class="bi bi-facebook facebook-icon"></i></a>
            </div>
        @endif
        @if($livret->linkedin)
            <div class="col-md-2">
                <a href="{{ $livret->linkedin }}" target="_blank"><i class="bi bi-linkedin linkedin-icon"></i></a>
            </div>
        @endif
        @if($livret->twitter)
            <div class="col-md-2">
                <a href="{{ $livret->twitter }}" target="_blank"><i class="bi bi-twitter twitter-icon"></i></a>
            </div>
        @endif
        @if($livret->tripadvisor)
            <div class="col-md-2">
                <a href="{{ $livret->tripadvisor }}" target="_blank"><i class="bi bi-globe tripadvisor-icon"></i></a>
            </div>
        @endif
    </div>
    <script>
        $('#homeInfosModal .btn-close').click(function () {
            $id = $('#homeInfosModal').data('livret');
            sessionStorage.setItem('homeInfosModalLivret', $id);
        });
        $(document).ready(function () {
            $id = $('#homeInfosModal').data('livret');
            if (sessionStorage.getItem('homeInfosModalLivret') != $id) {
                $('#homeInfosModal').modal('show');
            }
        });

    </script>
</footer>
</body>
</html>
