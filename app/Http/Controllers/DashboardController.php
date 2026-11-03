<?php

namespace App\Http\Controllers;

use App\Services\Dte\Post\DtePostManager;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {  
        
        $DtePostManager = new DtePostManager();
        $DtePostManager->post();
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://dev-api.haulmer.com/v2/dte/document',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{"response":["PDF","FOLIO"],"dte":{"Encabezado":{"IdDoc":{"TipoDTE":33,"Folio":0,"FchEmis":"2026-02-10","TpoTranCompra":"1","TpoTranVenta":"1","FmaPago":"2"},"Emisor":{"RUTEmisor":"76795561-8","RznSoc":"HAULMER SPA","GiroEmis":"VENTA AL POR MENOR POR CORREO, POR INTERNET Y VIA TELEFONICA","Acteco":"479100","DirOrigen":"ARTURO PRAT 527   CURICO","CmnaOrigen":"Curicó","Telefono":"0 0","CdgSIISucur":"81303347"},"Receptor":{"RUTRecep":"76430498-5","RznSocRecep":"HOSTY SPA","GiroRecep":"ACTIVIDADES DE CONSULTORIA DE INFORMATIC","DirRecep":"ARTURO PRAT 527 3 pis OF 1","CmnaRecep":"Curicó"},"Totales":{"MntNeto":2000,"TasaIVA":"19","IVA":380,"MntTotal":2380,"MontoPeriodo":2380,"VlrPagar":2380}},"Detalle":[{"NroLinDet":1,"NmbItem":"item","QtyItem":1,"PrcItem":2000,"MontoItem":2000}]}}
        ',
        CURLOPT_HTTPHEADER => array(
            'apikey: 928e15a2d14d4a6292345f04960f4bd3'
        ),
        ));

        $response = curl_exec($curl);

        dd($response);


        return view('dashboard');
    }
}
