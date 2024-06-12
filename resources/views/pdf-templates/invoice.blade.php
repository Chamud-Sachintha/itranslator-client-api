<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <style>
        body{
            margin-top:20px;
            color: #484b51;
        }
        .text-secondary-d1 {
            color: #728299!important;
        }
        .page-header {
            margin: 0 0 1rem;
            padding-bottom: 1rem;
            padding-top: .5rem;
            border-bottom: 1px dotted #e2e2e2;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-pack: justify;
            justify-content: space-between;
            -ms-flex-align: center;
            align-items: center;
        }
        .page-title {
            padding: 0;
            margin: 0;
            font-size: 1.75rem;
            font-weight: 300;
        }
        .brc-default-l1 {
            border-color: #dce9f0!important;
        }

        .ml-n1, .mx-n1 {
            margin-left: -.25rem!important;
        }
        .mr-n1, .mx-n1 {
            margin-right: -.25rem!important;
        }
        .mb-4, .my-4 {
            margin-bottom: 1.5rem!important;
        }

        hr {
            margin-top: 1rem;
            margin-bottom: 1rem;
            border: 0;
            border-top: 1px solid rgba(0,0,0,.1);
        }

        .text-grey-m2 {
            color: #888a8d!important;
        }

        .text-success-m2 {
            color: #86bd68!important;
        }

        .font-bolder, .text-600 {
            font-weight: 600!important;
        }

        .text-110 {
            font-size: 110%!important;
        }
        .text-blue {
            color: #478fcc!important;
        }
        .pb-25, .py-25 {
            padding-bottom: .75rem!important;
        }

        .pt-25, .py-25 {
            padding-top: .75rem!important;
        }
        .bgc-default-tp1 {
            /* background-color: rgba(121,169,197,.92)!important; */
            background-color: #7c0000 !important;
        }
        .bgc-default-l4, .bgc-h-default-l4:hover {
            /* background-color: #f3f8fa!important; */
            background-color: #ff0000;
        }
        .page-header .page-tools {
            -ms-flex-item-align: end;
            align-self: flex-end;
        }

        .btn-light {
            color: #757984;
            background-color: #f5f6f9;
            border-color: #dddfe4;
        }
        .w-2 {
            width: 1rem;
        }

        .text-120 {
            font-size: 120%!important;
        }
        .text-primary-m1 {
            color: #4087d4!important;
        }

        .text-danger-m1 {
            color: #dd4949!important;
        }
        .text-blue-m2 {
            color: #68a3d5!important;
        }
        .text-150 {
            font-size: 150%!important;
        }
        .text-60 {
            font-size: 60%!important;
        }
        .text-grey-m1 {
            color: #7b7d81!important;
        }
        .align-bottom {
            vertical-align: bottom!important;
        }

    </style>
</head>

<body>
    {{-- <h5 class="card-header">තෝරාගත් සේවාවන් පිලිබදව විස්තර.</h5> --}}
    <div class="card-body">
        <div class="page-content container">
            <div class="page-header text-blue-d2">
                <h1 class="page-title text-secondary-d1">
                    Invoice
                    <small class="page-info">
                        <i class="fa fa-angle-double-right text-80"></i>
                        {{ $data['invoiceNo'] }}
                    </small>
                </h1>

                {{-- <div class="page-tools">
                    <div class="action-buttons">
                        <a class="btn bg-white btn-light mx-1px text-95" href="#" (click)="printInvoice()">
                            <i class="mr-1 fa fa-print text-primary-m1 text-120 w-2"></i>
                            Print
                        </a>
                        <button class="btn bg-white btn-light mx-1px text-95" (click)="exportPDF()">
                            <i class="mr-1 fa fa-file-pdf-o text-danger-m1 text-120 w-2"></i>
                            Export
                        </button>
                    </div>
                </div> --}}
            </div>

            <div class="container px-0">
                <div class="row mt-4">
                    <div class="col-12 col-lg-12">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-center text-150">
                                    <img src="data:image/png;base64,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"
                                        style="width: 40px;" alt="">&nbsp;
                                    <span class="text-default-d3"
                                        style="font-size: 35px; color: red;">itranslator.com</span>
                                </div>
                            </div>
                        </div>
                        <!-- .row -->

                        <hr class="row brc-default-l1 mx-n1 mb-4" />
                        <hr />

                        <div class="row">
                            <div class="col-sm-6">
                                <div>
                                    <span class="text-sm text-grey-m2 align-middle">To:</span>
                                    <span class="text-600 text-110 text-blue align-middle">&nbsp;{{ $data['clientName'] }}</span>
                                </div>
                                <div class="text-grey-m2">
                                    <div class="my-1">
                                        {{ $data["address"] }}
                                    </div>
                                    <!-- <div class="my-1">
                                        State, Country
                                    </div> -->
                                    <div class="my-1"><i class="fa fa-phone fa-flip-horizontal text-secondary"></i> <b
                                            class="text-600">{{ $data['mobileNumber'] }}</b></div>
                                </div>
                            </div>
                            <!-- /.col -->
    
                            <div class="text-95 col-sm-6 align-self-start d-sm-flex justify-content-end">
                                <hr class="d-sm-none" />
                                <div class="text-grey-m2">
                                    <div class="mt-1 mb-2 text-secondary-m1 text-600 text-125">
                                        Invoice
                                    </div>
    
                                    <div class="my-2"><i class="fa fa-circle text-blue-m2 text-xs mr-1"></i> <span
                                            class="text-600 text-90">Delivery Method:</span>
                                        <p>{{ $data["deliveryType"] }}</p>
                                    </div>
    
                                    <div class="my-2"><i class="fa fa-circle text-blue-m2 text-xs mr-1"></i> <span
                                            class="text-600 text-90">Issue Date:</span> {{ date('Y-m-d H:i:s') }}</div>
    
                                    <div class="my-2"><i class="fa fa-circle text-blue-m2 text-xs mr-1"></i> <span
                                            class="text-600 text-90">Status:</span> <span
                                            class="badge badge-warning badge-pill px-25">Unpaid</span></div>
                                </div>
                            </div>
                            <!-- /.col -->
                        </div>

                        <div class="mt-4">
                            <div class="row text-600 text-white bgc-default-tp1 py-25">
                                <div class="d-none d-sm-block col-1">#</div>
                                <div class="col-9 col-sm-5">Document Title</div>
                                <div class="d-none d-sm-block col-4 col-sm-2"></div>
                                <div class="d-none d-sm-block col-sm-2">Pages</div>
                                <div class="col-2">Amount</div>
                            </div>
    
                            <div class="text-95 text-secondary-d3">
                                @foreach ($data['documentObjectArray'] as $item)
                                    <div class="row mb-2 mb-sm-0 py-25">
                                        <div class="d-none d-sm-block col-1">1</div>
                                        <div class="col-9 col-sm-5">{{ $item['translationTitle'] }}</div>
                                        <div class="d-none d-sm-block col-2">
                                            <!-- empty space dont remove it -->
                                        </div>
                                        <div class="d-none d-sm-block col-2 text-95">{{ $item['pages'] }}</div>
                                        <div class="col-2 text-secondary-d2">LKR. 1000 .00</div>
                                    </div>
                                @endforeach
                            </div>
    
                            <div class="row border-b-2 brc-default-l2"></div>
    
                            <div class="row mt-3">
                                <div class="col-12 col-sm-7 text-grey-d2 text-95 mt-2 mt-lg-0">
                                    Extra note such as company or payment information...
                                </div>
    
                                <div class="col-12 col-sm-5 text-grey text-90 order-first order-sm-last">
                                    <div class="row my-2 align-items-center bgc-primary-l3 p-2">
                                        <div class="col-7 text-right">
                                            Total Amount
                                        </div>
                                        <div class="col-5">
                                            <span class="text-150 text-success-d3 opacity-2">LKR. 1000 .00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                            <hr />
    
                            <div>
                                <span class="text-secondary-d1 text-105">Thank you for your Order</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>