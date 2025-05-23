@extends('layouts.dashboard')
@section('dashboard_title', 'Editer mon livret')

@section('dashboard_content')
    <style>
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
    </style>
    <h1>Editer le livret - <strong>{{$livret->livret_name}}</strong></h1>
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{session('success')}}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-error" role="alert">
            {{session('error')}}
        </div>
    @endif
    <div class="row container">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modulesOrderModal">
            Changer l'ordre des modules
        </button>
        <hr>
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#textDesignModal">
            Changer le design des textes
        </button>
        <div class="col-md-3 col-sm-4">
            <div class="card text-center">
                <i class="bi bi-wifi"></i>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#wifiModal">
                    Wifi
                </button>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="card text-center">
                <i class="bi bi-key"></i>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#digicodeModal">
                    Digicode
                </button>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="card text-center">
                <i class="bi bi-geo-alt"></i>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#placesGroupsModal">
                    Groupes de lieux
                </button>
            </div>
        </div>
        @if(!$livret->placeGroups->isEmpty())
            <div class="col-md-3 col-sm-4">
                <div class="card text-center">
                    <i class="bi bi-geo-alt"></i>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#placesModal">
                        Autour de moi
                    </button>
                </div>
            </div>
        @endif
        <div class="col-md-3 col-sm-4">
            <div class="card text-center">
                <i class="bi bi-telephone"></i>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#phonesModal">
                    Numéro utiles
                </button>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="card text-center">
                <i class="bi bi-info-circle"></i>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#utilsInfosModal">
                    Infos pratiques
                </button>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="card text-center">
                <i class="bi bi-arrow-up-right"></i>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startInfosModal">
                    Info arrivée
                </button>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="card text-center">
                <i class="bi bi-arrow-down-left"></i>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#endInfosModal">
                    Info départ
                </button>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="card text-center">
                <i class="bi bi-envelope"></i>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#homeInfosModal">
                    Mot d'accueil
                </button>
            </div>
        </div>
    </div>
    @include('dashboard.partials.module_wifi')
    @include('dashboard.partials.module_digicode')
    @include('dashboard.partials.module_utils_phone')
    @include('dashboard.partials.module_utils_infos')
    @include('dashboard.partials.module_start_infos')
    @include('dashboard.partials.module_end_infos')
    @include('dashboard.partials.module_home_infos')
    @include('dashboard.partials.module_nearby_places')
    @include('dashboard.partials.module_places_groups')
    @include('dashboard.partials.module_order')
    @include('dashboard.partials.change_text_design_modal')
@endsection

@section('footer_script')
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            $("tbody").sortable({
                handle: '.move_item',
                update: function(event, ui) {
                    var newOrder = [];
                    $(this).find('tr').each(function(index) {
                        var moduleName = $(this).find('td:last-child').text().trim();
                        newOrder.push({order: index, module: moduleName});
                    });
                    $.ajax({
                        url: '/dashboard/update-order',
                        method: 'POST',
                        data: {
                            order: newOrder,
                            _token: '{{ csrf_token() }}'
                        }
                    }).done(function() {
                        let i = 0;
                        $(".module_order_tr").each(function(index) {
                            $(this).find('td:nth-child(2)').text(i);
                        console.log(i);
                            i++;
                        });
                    });
                }
            });
        });
    </script>
@endsection
