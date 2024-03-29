@extends('layouts.dashboard')
@section('dashboard_title')
Dashboard
@endsection
@section('dashboard_breadcum')
<li class="breadcrumb-item" aria-current="page"><a href="javascript:void(0);">Ads</a></li>
<li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0);"></a>Create</li>
@endsection
@section('dashboard_content')
<script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="{!! asset('back') !!}/css/forms/theme-checkbox-radio.css">
<link href="{!! asset('back') !!}/plugins/loaders/custom-loader.css" rel="stylesheet" type="text/css" />
<div class="col-lg-12 col-12 layout-spacing">
    @include('Alerts.alerts')
    <div id="alert">

    </div>
    <div class="">
        <div class="widget-content widget-content-area border-top-tab">
            @include('components.dashbaord.tabs')
            <div class="tab-content" id="borderTopContent">
                <form action="{!! route('dashboard.ads.create.three.payment') !!}" method="post">
                    @csrf
                    <input type="hidden" name="adsid" value="{{ $ads->id }}">
                    <div class="tab-pane fade show active" id="border-top-contact" role="tabpanel" aria-labelledby="border-top-contact-tab">
                        <div class="row">
                            <div class="col-md-12">
                                <p> <b> Total Amount: </b> {{ $ads->package }} ETH </p>
                                <input type="hidden" name="amount" id="inp_amount" value="{{ $ads->package }}" aria-describedby="helpId">
                            </div>
                            <div class="col-md-12 mb-5">
                                <button type="button" class="btn btn-success" name="button" onClick="startProcess()">Connect Wallet</button>
                                <div class="loader multi-loader mx-auto"></div>
                            </div>


                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    function startProcess() {
        if ($('#inp_amount').val() > 0) {
            // run metamsk functions here
            EThAppDeploy.loadEtherium();
        } else {
            $("#alert").append(`<div class="alert alert-danger">Please Enter Valid Amount</div>`)
        }
    }


    EThAppDeploy = {
        loadEtherium: async () => {
            if (typeof window.ethereum !== 'undefined') {
                EThAppDeploy.web3Provider = ethereum;
                EThAppDeploy.requestAccount(ethereum);
            } else {
                $("#alert").append(`<div class="alert alert-danger">Not able to locate an Ethereum connection, please install a Metamask wallet</div>`)
            }
        },
        /****
         * Request A Account
         * **/
        requestAccount: async (ethereum) => {
            ethereum
                .request({
                    method: 'eth_requestAccounts'
                })
                .then((resp) => {
                    //do payments with activated account
                    EThAppDeploy.payNow(ethereum, resp[0]);
                })
                .catch((err) => {
                    // Some unexpected error.
                    $("#alert").append(`<div class="alert alert-danger">` + err.message + `</div>`)
                });
        },
        /***
         *
         * Do Payment
         * */
        payNow: async (ethereum, from) => {
            var amount = $('#inp_amount').val();
            ethereum
                .request({
                    method: 'eth_sendTransaction',
                    params: [{
                        from: from,
                        to: "0x558572A544f6d6030b9a23C98d689D48D2EF4d9a",
                        value: '0x' + ((amount * 1000000000000000000).toString(16)),
                    }, ],
                })
                .then((txHash) => {
                    if (txHash) {
                        $("#alert").append(`<div class="alert alert-success"> Your payment success (Click here to verify 👉) <a href="https://ropsten.etherscan.io/tx/` + txHash + `" target="_blank">` + txHash + `</a></div>`)
                        let hashtx = txHash;
                        let adsid = $("input[name=adsid]").val();
                        let _token = $('meta[name="csrf-token"]').attr('content');

                        $.ajax({
                            url: "/payment",
                            type: "POST",
                            data: {
                                hashtx: hashtx,
                                adsid: adsid,
                                _token: _token
                            },
                            success: function(response) {
                              window.location.replace('/dashboard/ads');
                            },
                            error: function(error) {
                                alert(error.message);
                            }
                        });
                    } else {
                        $("#alert").append(`<div class="alert alert-danger">Something went wrong. Please try again</div>`)
                    }
                })
                .catch((error) => {
                    $("#alert").append(`<div class="alert alert-danger">` + error.message + `</div>`)
                });
        },
    }
</script>

@endsection
