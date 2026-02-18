<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalTransaction extends Model
{
    use HasFactory;

    protected $table = 'local_transaction';
    protected $fillable = ['TransationDate', 'TransactionType', 'Total', 'PaymentType', 'fiscal_invoice'];

   public function compaqIntegration()
    {
        return $this->hasOne(CompaqIntegration::class, 'id', 'integrate_cp');
    }

    public static function resumenPorDia(?string $from = null, ?string $until = null)
    {
        $from  = $from  ? Carbon::parse($from)->startOfDay() : now()->startOfMonth();
        $until = $until ? Carbon::parse($until)->endOfDay()   : now()->endOfMonth();

        return self::query()
            ->from('local_transaction as t1')
            ->leftJoin('client_membership as t2', function ($join) {
                $join->on('t1.Membership', '=', 't2._id')
                     ->whereRaw('LENGTH(t1.Membership) = 24');
            })
            ->leftJoin('clients as t3', 't2.client_id', '=', 't3._id')
            ->selectRaw("
                DATE(t1.TransationDate) AS fecha,
                COUNT(*) AS total_eventos,

                -- Pago (TransactionType=2 y Total <> 0)
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total <> 0 THEN 1 ELSE 0 END) AS lavados_paquete,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total <> 0 AND t1.Package = '612f057787e473107fda56aa' THEN 1 ELSE 0 END) AS lavados_express,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total <> 0 AND t1.Package = '612f067387e473107fda56b0' THEN 1 ELSE 0 END) AS lavados_basico,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total <> 0 AND t1.Package IN ('61344b9137a5f00383106c84','612f1c4f30b90803837e7969') THEN 1 ELSE 0 END) AS lavados_ultra,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total <> 0 AND t1.Total <> 150 AND t1.Total <> 50  AND t1.Package IN( '612abcd1c4ce4c141237a356','61344bab37a5f00383106c88') THEN 1 ELSE 0 END) AS lavados_deluxe,
                SUM(CASE WHEN t1.TransactionType = 2  AND t1.Total = 150 AND t1.Package = '612abcd1c4ce4c141237a356' THEN 1 ELSE 0 END) AS promo150,
                SUM(CASE WHEN t1.TransactionType = 2  AND t1.Total = 50 AND t1.Package = '612abcd1c4ce4c141237a356' THEN 1 ELSE 0 END) AS promo50,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total <> 0 THEN t1.Total ELSE 0 END) AS suma_total_tipo2,

                -- Membresía (Total = 0 y PaymentType != 3)
                SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 AND t1.PaymentType <> 3 THEN 1 ELSE 0 END) AS lavados_membresia,
                SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 AND t1.PaymentType <> 3 AND t1.Package = '61344ae637a5f00383106c7a' THEN 1 ELSE 0 END) AS lavados_express_membresia,
                SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 AND t1.PaymentType <> 3 AND t1.Package = '61344b5937a5f00383106c80' THEN 1 ELSE 0 END) AS lavados_basico_membresia,
                SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 AND t1.PaymentType <> 3 AND t1.Package = '61344b9137a5f00383106c84' THEN 1 ELSE 0 END) AS lavados_ultra_membresia,
                SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 AND t1.PaymentType <> 3 AND t1.Package = '61344bab37a5f00383106c88' THEN 1 ELSE 0 END) AS lavados_deluxe_membresia,

                -- Compra/renovación de membresía
                SUM(CASE WHEN t1.TransactionType IN (0) THEN 1 ELSE 0 END) AS compra_membresia,
                SUM(CASE WHEN t1.TransactionType IN (1) THEN 1 ELSE 0 END) AS renovacion_membresia,
                SUM(CASE WHEN t1.TransactionType IN (0) AND t1.Total <> 0 THEN t1.Total ELSE 0 END) AS sum_compra_membresia,
                SUM(CASE WHEN t1.TransactionType IN (1) AND t1.Total <> 0 THEN t1.Total ELSE 0 END) AS sum__renovacion_membresia,

                -- Cortesía
                SUM(CASE WHEN t1.TransactionType = 2 AND t1.Total = 0 AND t1.PaymentType = 3 AND t1.Membership NOT IN ('f104f389-103f-44fa-bd41-f948af1ecbb7','92a43e72-4e6e-4ecc-8b80-b6a921166cdb','e7089f5a-8d86-40e2-8b87-c092fd026f5d' ,'81e4abd3-0ead-4d5b-a834-08a1e7a6a9ca') THEN 1 ELSE 0 END) AS lavados_cortesia,

                -- Total del día
                SUM(t1.Total) AS suma_total_dia,
                SUM(CASE WHEN t1.Membership = 'f104f389-103f-44fa-bd41-f948af1ecbb7' THEN 1 ELSE 0 END ) AS QrMembresiaDeluxe,
                SUM(CASE WHEN t1.Membership = '92a43e72-4e6e-4ecc-8b80-b6a921166cdb' THEN 1 ELSE 0 END ) AS QrMembresiaExpress,
                SUM(CASE WHEN t1.Membership = 'e7089f5a-8d86-40e2-8b87-c092fd026f5d' THEN 1 ELSE 0 END ) AS QrMembresiaBasico,
                SUM(CASE WHEN t1.Membership = '81e4abd3-0ead-4d5b-a834-08a1e7a6a9ca' THEN 1 ELSE 0 END ) AS QrMembresiaUltra
            ")
            ->whereBetween('t1.TransationDate', [$from, $until])
            ->groupBy(DB::raw('DATE(t1.TransationDate)'))
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public static function indicadores_pagos_table(?string $from = null, ?string $until = null){
        $resultados = DB::select("
                SELECT 
                    DATE(t1.TransationDate) AS fecha,
                    -- Conteo total de eventos por día
                    COUNT(*) AS total_eventos,
                    
                    -- Efectivo
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.PaymentType = 0 AND t1.Total != 0 THEN t1.Total ELSE 0 END) AS suma_total_efectivo,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.PaymentType = 0 AND t1.Total != 0 AND t1.Atm = 'AQUA01' THEN t1.Total ELSE 0 END) AS suma_total_cajero1,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.PaymentType = 0 AND t1.Total != 0 AND t1.Atm = 'AQUA02' THEN t1.Total ELSE 0 END) AS suma_total_cajero2,
                    
                    -- Tarjetas paquetes
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.PaymentType != 0 AND t1.Total != 0 THEN t1.Total ELSE 0 END) AS suma_targetas_paquetes,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.PaymentType != 0 AND t1.Total != 0 AND t1.Atm = 'AQUA01' THEN t1.Total ELSE 0 END) AS suma_targetas_cajero_1,
                    SUM(CASE WHEN t1.TransactionType = 2 AND t1.PaymentType != 0 AND t1.Total != 0 AND t1.Atm = 'AQUA02' THEN t1.Total ELSE 0 END) AS suma_targetas_cajero_2,
                    
                    -- Membresías
                    SUM(CASE WHEN t1.TransactionType IN (1, 0) AND t1.PaymentType != 0 AND t1.Total != 0 THEN t1.Total ELSE 0 END) AS suma_compra_membrecias,
                    SUM(CASE WHEN t1.TransactionType IN (0) AND t1.PaymentType != 0 AND t1.Total != 0 AND t1.Atm = 'AQUA01' THEN t1.Total ELSE 0 END) AS suma_comra_membresia_cajero_1,
                    SUM(CASE WHEN t1.TransactionType IN (0) AND t1.PaymentType != 0 AND t1.Total != 0 AND t1.Atm = 'AQUA02' THEN t1.Total ELSE 0 END) AS suma_compra_membresia_cajero_2,
                    SUM(CASE WHEN t1.TransactionType IN (1) AND t1.PaymentType != 0 AND t1.Total != 0 AND t1.Atm = 'AQUA01' THEN t1.Total ELSE 0 END) AS suma_renovacion_membresia_cajero_1,
                    SUM(CASE WHEN t1.TransactionType IN (1) AND t1.PaymentType != 0 AND t1.Total != 0 AND t1.Atm = 'AQUA02' THEN t1.Total ELSE 0 END) AS suma_renovacion_membresia_cajero_2,
                    SUM(CASE WHEN t1.TransactionType IN ( 1,0,2) AND t1.PaymentType !=0 AND t1.Total != 0  THEN t1.Total ELSE 0 END) AS suma_procepago,
                    
                    -- Total del día
                    SUM(t1.Total) AS suma_total_dia
                FROM `local_transaction` t1
                LEFT JOIN client_membership t2 ON t1.Membership = t2._id AND LENGTH(t1.Membership) = 24 
                LEFT JOIN clients t3 ON t2.client_id = t3._id
                WHERE t1.TransationDate BETWEEN ? AND ?
                GROUP BY DATE(t1.TransationDate)
                ORDER BY fecha DESC
            ", [$from, $until]);
        return $resultados;
    }

    // public static function indicadores_membresias_table(?string $from = null, ?string $until = null){
    //     $resultados = DB::select("
    //              SELECT
    //                 CONCAT(MAX(t2.first_name),' ',MAX(t2.last_name)) AS cliente,
    //                 t1.UserId,
    //                 MAX(t3.name) AS package,
    //                 CASE MAX(t4.membership_id)
    //                                 WHEN '612f057787e473107fda56aa' THEN 'Express'
    //                                 WHEN '61344ae637a5f00383106c7a' THEN 'Express'
    //                                 WHEN '612f067387e473107fda56b0' THEN 'Básico'
    //                                 WHEN '61344b5937a5f00383106c80' THEN 'Básico'
    //                                 WHEN '612f1c4f30b90803837e7969' THEN 'Ultra'
    //                                 WHEN '61344b9137a5f00383106c84' THEN 'Ultra'
    //                                 WHEN '61344bab37a5f00383106c88' THEN 'Delux'
    //                                 WHEN '612abcd1c4ce4c141237a356' THEN 'Delux'
    //                                 ELSE 'N/A'
    //                             END AS package_name,
    //                 COUNT(*) AS total
    //                 FROM `orders` t1
    //                 LEFT JOIN `clients` t2 ON t1.UserId = t2._id
    //                 LEFT JOIN `packages` t3 on t1.package_id = t3._id
    //                 LEFT JOIN `client_membership` t4 ON t2.active_membership = t4._id
    //                 WHERE 
    //                 t1.created_at BETWEEN ? AND ?
    //                 AND t1.OrderType = 1
    //                 GROUP BY t1.UserId
    //         ", [$from, $until]);
    //     return $resultados;
    // }

    public static function indicadores_membresias_table(?string $from = null, ?string $until = null)
    {
        $sql = "
            WITH latest AS (
                SELECT
                    cm.client_id,
                    cm.membership_id,
                    cm.start_date,
                    cm.end_date,
                    ROW_NUMBER() OVER (
                        PARTITION BY cm.client_id
                        ORDER BY cm.start_date DESC
                    ) AS rn
                FROM client_membership cm
            ),
            orders_agg AS (
                SELECT 
                    o.UserId AS client_id,
                    COUNT(*) AS total
                FROM orders o
                WHERE 
                    o.OrderType = 1
                    AND o.created_at BETWEEN ? AND ?
                    -- Si prefieres fin exclusivo: 
                    -- AND o.created_at >= ? AND o.created_at < ?
                GROUP BY o.UserId
            )
            SELECT
                l.client_id,
                CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,'')) AS cliente,
                COALESCE(oa.total, 0) AS total_orders,
                CASE l.membership_id
                    WHEN '612f057787e473107fda56aa' THEN 'Express'
                    WHEN '61344ae637a5f00383106c7a' THEN 'Express'
                    WHEN '612f067387e473107fda56b0' THEN 'Básico'
                    WHEN '61344b5937a5f00383106c80' THEN 'Básico'
                    WHEN '612f1c4f30b90803837e7969' THEN 'Ultra'
                    WHEN '61344b9137a5f00383106c84' THEN 'Ultra'
                    WHEN '61344bab37a5f00383106c88' THEN 'Delux'
                    WHEN '612abcd1c4ce4c141237a356' THEN 'Delux'
                    ELSE 'N/A'
                END AS package_name,
                l.start_date,
                l.end_date,
                c.first_name,
                c.last_name
            FROM latest l
            LEFT JOIN orders_agg oa
                ON oa.client_id = l.client_id
            LEFT JOIN clients c
                ON c._id = l.client_id
            WHERE l.rn = 1
            ORDER BY oa.total DESC, cliente ASC
        ";

        return DB::select($sql, [$from, $until]);
    }
    public static function membresiasCajero(?string $from = null, ?string $until = null)
    {
        $from  = $from  ? Carbon::parse($from)->startOfDay() : now()->startOfDay();
        $until = $until ? Carbon::parse($until)->endOfDay()   : now()->endOfDay();

        return self::query()
            ->selectRaw("
                _id,
                TransationDate,
                DATE(TransationDate) AS fecha,
                TIME(DATE_ADD(TransationDate, INTERVAL 1 HOUR)) AS hora,
                CASE TransactionType 
                    WHEN 0 THEN 'Compra'
                    WHEN 1 THEN 'Renovacion'
                    ELSE 'N/A'
                END AS tipo_transaccion,
                CASE PaymentType 
                    WHEN 0 THEN 'Efectivo'
                    WHEN 1 THEN 'Tarjeta Debito'
                    WHEN 2 THEN 'Tarjeta Credito'
                    WHEN 3 THEN 'Cortesia'
                    ELSE 'N/A'
                END AS tipo_pago,
                Package,
                Membership,
                Total,
                Atm
            ")
            ->whereBetween('TransationDate', [$from, $until])
            ->whereIn('TransactionType', [0, 1]) // Solo compras (0) y renovaciones (1)
            ->orderBy('_id', 'desc')
            ->get();
    }

    public static function pagosCajero(?string $from = null, ?string $until = null)
    {

        $sql = "SELECT 
            t1._id,t1.local_transaction_id,
            DATE(t1.TransationDate) AS fecha,
            TIME(DATE_ADD(t1.TransationDate, INTERVAL 1 HOUR)) AS hora,  -- aquí le sumamos 1 hora
            CONCAT(t3.first_name,\" \", t3.last_name) AS cliente,
            CASE t1.package
                    WHEN '612f057787e473107fda56aa' THEN 'Express'
                    WHEN '61344ae637a5f00383106c7a' THEN 'Express'

                    WHEN '612f067387e473107fda56b0' THEN 'Básico'
                    WHEN '61344b5937a5f00383106c80' THEN 'Básico'

                    WHEN '612f1c4f30b90803837e7969' THEN 'Ultra'
                    WHEN '61344b9137a5f00383106c84' THEN 'Ultra'

                    WHEN '61344bab37a5f00383106c88' THEN 'Delux'
                    WHEN '612abcd1c4ce4c141237a356' THEN 'Delux'
                    ELSE 'N/A'  -- por si aún quieres conservar el nombre si existe
                END AS `package_name`,
            t1.Atm,
            case t1.PaymentType 
                when 0 then 'Efectivo'
                When 1 then 'Targeta Debito'
                When 2 then 'Targeta Credito'
                When 3 then 'Garantia'
            ELSE 'N/A'  
            END AS `method`,
            case t1.TransactionType 
                when 0 then 'Compra Membresia'
                When 1 then 'Renovacion Membresia'
                When 2 then 'Lavado'
            ELSE 'N/A'  
            END AS `tipo_transaccion`,
            t1.TransactionType,t1.PaymentType,t1.Total,t1.TotalPayed,t1.`Change`,t1.Membership,t1.Package,t1.CadenaFacturacion,
            t1.fiscal_invoice,
            t1.fiscal_account_id,
            t4.rfc, t4.company_name
            FROM `local_transaction` t1
            LEFT JOIN client_membership t2  ON t1.Membership = t2._id   AND LENGTH(t1.Membership) = 24 
            LEFT JOIN clients t3 ON t2.client_id = t3._id
            LEFT JOIN fiscal_accounts t4 ON t1.fiscal_account_id = t4.id
            WHERE
            t1.TransationDate BETWEEN ? AND ?
            ORDER by
            --  t1._id DESC,
            fecha desc,hora desc 

        ";

        return DB::select($sql, [$from, $until]);
    }
    
}