<?php
namespace App\Models;

class GeneralCatalogs {

	function __construct() { }

	var $role_type = array(
		'1' => 'SUPERADMIN',
		'2' => 'ADMINISTRATOR',
		'3' => 'MANAGER'
	);

	var $facility_type = array(
		'1' => 'MISIONES',
		'2' => 'CEDIS'
	);

	
	var $day_of_week = array(
		'1' => 'Lunes',
		'2' => 'Martes',
		'3' => 'Miércoles',
		'4' => 'Jueves',
		'5' => 'Viernes',
		'6' => 'Sábado',
		'0' => 'Domingo',
	);

	var $weather_description = array(
		'Partly cloudy'           => 'Parcialmente nublado',
		'Sunny'                   => 'Soleado',
		'Clear '                  => 'Despejado',
		'Clear'                   => 'Despejado',
		'Overcast'               => 'Nublado/Cubierto',
		'Overcast '               => 'Nublado/Cubierto',
		'Blowing Widespread Dust' => 'Polvo generalizado en suspensión'
	);

	var $business_hours = array(
		'06' => '6:00 am',
		'07' => '7:00 am',
		'08' => '8:00 am',
		'09' => '9:00 am',
		'10' => '10:00 am',
		'11' => '11:00 am',
		'12' => '12:00 pm',
		'13' => '1:00pm',
		'14' => '2:00pm',
		'15' => '3:00pm',
		'16' => '4:00pm',
		'17' => '5:00pm',
		'18' => '6:00pm',
		'19' => '7:00pm',
		'20' => '8:00pm',
		'21' => '9:00pm',
	);

	var $month = array(
		'1' => 'Enero',
		'2' => 'Febrero',
		'3' => 'Marzo',
		'4' => 'Abril',
		'5' => 'Mayo',
		'6' => 'Junio',
		'7' => 'Julio',
		'8' => 'Agosto',
		'9' => 'Septiembre',
		'10' => 'Octubre',
		'11' => 'Noviembre',
		'12' => 'Diciembre'
	);

	var $transfer_status = array(
		'1' => 'EN PROCESO',
		'2' => 'CERRADO',
		'3' => 'PENDIENTE',
		'4' => 'RECIBIDA'
	);

	var $transfer_status_color = array(
		'1' => 'bg-info',
		'2' => 'bg-success',
		'3' => 'bg-warning',
		'4' => 'bg-primary'
	);

	public $inventory_status_color = [
        'suficiente' => 'bg-success',
        'bajo' => 'bg-warning',
    ];

    public $inventory_status = [
        'suficiente' => 'Suficiente',
        'bajo' => 'Bajo',
    ];

	var $OrderType= array(
		'1' => 'Uso de Membresía',
		'2' => 'Uso de Paquete'
	);

	//////paquetes
	var $package_type = array(
		'612f057787e473107fda56aa' => 'Express',
		'61344ae637a5f00383106c7a' => 'Express',
        
        '612f067387e473107fda56b0' => 'Básico',
        '61344b5937a5f00383106c80' => 'Básico',

        '612f1c4f30b90803837e7969' => 'Ultra',
        '61344b9137a5f00383106c84' => 'Ultra',

        '61344bab37a5f00383106c88' => 'Delux',
        '612abcd1c4ce4c141237a356' => 'Delux',
        //'4' => 'No conocido'
	);

	var $transactiontype_type = array(
		'0' => 'Compra de Membresía',
		'1' => 'Renovación de Membresía',
		'2' => 'Compra paquete',
		'3' => 'Cortesia'/////no hay 
	);
	
	var $membership_type = array(
	  '61344ae637a5f00383106c7a' => 'Express',
	  '61344b5937a5f00383106c80' => 'Basico',
	  '61344b9137a5f00383106c84' => 'Ultra',
	  '61344bab37a5f00383106c88' => 'Delux'
  );

	var $folio_payment_type = array(
		'0' => 'Efectivo',
        '1' => 'Tarjeta de Débito',
        '2' => 'Tarjeta de Crédito',
        '3' => 'Cortesía'
	);

	var $unit_measurement = array(
        '3' => 'Barril',
        '6' => 'Caja',
        '2' => 'Galón',
        '1' => 'Litro',
        '5' => 'Pallet',
        '4' => 'Paquete',
        '7' => 'Pieza'
	);

	var $payment_method = array(
		'Efectivo' 								=> '01',
		'Cheque nominativo' 					=> '02',
		'Transferencia electrónica de fondos' 	=> '03',
		'Tarjeta de Crédito' 					=> '04',
		'Monedero electrónico' 					=> '05',
		'Dinero electrónico' 					=> '06',
		'Vales de despensa' 					=> '08',
		'Dación en pago'		 				=> '12',
		'Pago por subrogación' 					=> '13',
		'Pago por consignación' 				=> '14',
		'Condonación' 							=> '15',
		'Compensación' 							=> '17',
		'Novación' 								=> '23',
		'Confusión' 							=> '24',
		'Remisión de deuda' 					=> '25',
		'Prescripción o caducidad' 				=> '26',
		'A satisfacción del acreedor' 			=> '27',
		'Tarjeta de Débito' 					=> '28',
		'Tarjeta de servicios' 					=> '29',
		'Aplicación de anticipos' 				=> '30',
		'Intermediario Pagos'					=> '31',
		'Por definir' 							=> '99'
	);

	var $regimens = array(
		'General de Ley Personas Morales'																=> '601',
		'Sueldos y Salarios e Ingresos Asimilados a Salarios' 											=> '605',
		'Arrendamiento' 																				=> '606',
		'Régimen de Enajenación o Adquisición de Bienes' 												=> '607',
		'Demás ingresos' 																				=> '608',
		'Residentes en el Extranjero sin Establecimiento Permanente en México' 							=> '610',
		'Ingresos por Dividendos (socios y accionistas)' 												=> '611',
		'Personas Físicas con Actividades Empresariales y Profesionales'								=> '612',
		'Ingresos por intereses'																		=> '614',
		'Régimen de los ingresos por obtención de premios'												=> '615',
		'Sin obligaciones fiscales'																		=> '616',
		'Incorporación Fiscal'																			=> '621',
		'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas'	=> '625',
		'Régimen Simplificado de Confianza'																=> '626',
	);

	var $payment_type = array(
		'Pago en una sola exhibición' => 'PUE',
		'Pago en parcialidades ó diferido' => 'PPD',
	);

	var $cfdi_use = array(
		'Adquisición de mercancias' 															=> 'G01',
		//'Devoluciones, descuentos o bonificaciones' 											=> 'G02',
		'Gastos en general' 																	=> 'G03',
		//'Construcciones' 																		=> 'I01',
		//'Mobilario y equipo de oficina por inversiones' 										=> 'I02',
		//'Equipo de transporte' 																	=> 'I03',
		//'Equipo de computo y accesorios' 														=> 'I04',
		//'Dados, troqueles, moldes, matrices y herramental' 										=> 'I05',
		//'Comunicaciones telefónicas' 															=> 'I06',
		//'Comunicaciones satelitales' 															=> 'I07',
		//'Otra maquinaria y equipo' 																=> 'I08',
		//'Honorarios médicos, dentales y gastos hospitalarios' 									=> 'D01',
		//'Gastos médicos por incapacidad o discapacidad' 										=> 'D02',
		//'Gastos funerales' 																		=> 'D03',
		//'Donativos' 																			=> 'D04',
		//'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)' 	=> 'D05',
		//'Aportaciones voluntarias al SAR' 														=> 'D06',
		//'Primas por seguros de gastos médicos' 													=> 'D07',
		//'Gastos de transportación escolar obligatoria' 											=> 'D08',
		//'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones' 	=> 'D09',
		//'Pagos por servicios educativos (colegiaturas)'	 										=> 'D10',
		'Por definir' 																			=> 'P01'
	);

	var $states_mx = array(
	    'AGS' => 'Aguascalientes',
	    'BC'  => 'Baja California',
	    'BCS' => 'Baja California Sur',
	    'CAMP' => 'Campeche',
	    'COAH' => 'Coahuila de Zaragoza',
	    'COL' => 'Colima',
	    'CHIS' => 'Chiapas',
	    'CHIH' => 'Chihuahua', 
	    'CDMX' => 'Ciudad de México', 
	    'DUR'  => 'Durango',
	    'GTO'  => 'Guanajuato',
	    'GRO'  => 'Guerrero',
	    'HID'  => 'Hidalgo',
	    'JAL'  => 'Jalisco',
	    'MEX'  => 'Estado de México',
	    'MIC'  => 'Michoacán de Ocampo',
	    'MOR'  => 'Morelos',
	    'NAY'  => 'Nayarit',
	    'NL'   => 'Nuevo León',
	    'OAX'  => 'Oaxaca',
	    'PUE'  => 'Puebla',
	    'QRO'  => 'Querétaro de Arteaga',
	    'SLP'  => 'San Luis Potosí',
	    'SIN'  => 'Sinaloa',
	    'SON'  => 'Sonora',
	    'TAB'  => 'Tabasco',
	    'TAMPS' => 'Tamaulipas',
	    'TLAX' => 'Tlaxcala',
	    'VER'  => 'Veracruz de Ignacio de la Llave',
	    'YUC'  => 'Yucatán',
	    'ZAC'  => 'Zacatecas'  
	);

	var $api_data_reference = array(

        # PRODUCCION
        'api'               => 'https://api.facturoporti.com.mx',
				'username'  => 'ACC220922GT0',
				'password'  => 'AQUA20PXT25',
        'timezone'          => '@Notiene1',
        'local_save_folder' => 'aqua-facturacion.ayaladigitalllc.com',
        'RFC'               => 'ACC220922GT0',
        'NombreRazonSocial' => 'AQUA CAR CLUB',
        'RegimenFiscal'     => '601',
        'CodigoPostal'      => '32030',

        'CSD' => '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',

        'CSDKey' => 'MIIFDjBABgkqhkiG9w0BBQ0wMzAbBgkqhkiG9w0BBQwwDgQIAgEAAoIBAQACAggAMBQGCCqGSIb3DQMHBAgwggS+AgEAMASCBMg98eLiNgZaraMlvjKTWXDfjSQJHVGeL1zZU3Ug6kit7iT6LcatjyBIQwPod+hfAcLIsB9MJoZpz9W+Ud/CldQB922K8qBn98Ar6uvtQqeCjKaYiXTccWl2LgB46HSt25LpsUu4JTyecLNcBlvZGS5luBbE+0ZdI8IoIgtYXo5E4SnBT9B6+8Y08P0niyRQ/M1yPjnRfsKqX/iSK9Pym6xoliquucR4rLwNh1p00ohZ7QXDUHOE56I2FIGV1UDVNPWMvyAoKzM5iBgv8Ib519AjArMznMJFHXHOipyqUYOFxI7Mu+J4NifweKimZnT0Bi41/3DuATwHVSkQMv2BhHEvSWLRHqSrW/fDqN4AAZq9kTBiPdOZL7PIWo19EtseGnXdv4OJiEuo6yF8Trxy/E/OdElSfjvMAyou4siqQ0ML1CSuilExHQ0vEIngDJj85MNy8epqPhnkOTLEapaJuKIQWyDfrOrotzebN6LxSv3Czmaybjv8f6Y8IurcId0Nnr47SpQDkr/2T8k//5oHdV7Nf3uhe4SPBLuNn5WZOcASGTDBaR6MXGHwafV4frR5hk+5VmOO8rXpo0LMgkv18fca8PPB5CZtGCksxOaRqPpF+0Cx4kY3k1OswyAJoBC/mbsed26duCztMa/i/IkCobqOtzFIdZ+X+WWH4gE1VLwUwUGdmOdWNFg0OjNsVrsabBKyQ0vqLtHZmvTvx5Sqct6PuLcwrNUvXno1z3GCbXXMrt1LOtw8cjYfwn9xrd27t4KcN8ECJLGWmb1BH4s96Zp/FWhjaHBvq6sRoV56PwkZ6bn3VJb6hSsRNeL01SwaBVLB90VeAzp67zJl8w0xR1XKxRiJonDW+60CsYGQmpH2LtqRheq0JJ2f9yH/2Pu4NxLTNRDGEJnJ+f1ZGjoDkVBUSV9g7n6GXZeBmUGAFQ19nO0fY0G/YAPridcxDOQc8JQP8DZyaBwOTfFVNgbl0Tcy0/U88GFsDKVj8E3sH5fIxzaH6LlM1hSQzIuYpD7rHHUa/Js7P4UbxhWGoS9gwWQL8I5HU10pBo2GqKaps4+P/GSBCntAJiL8XYSNrBJAPDkmqUimGtFJXvph23eLz6NPk5F9Oy3DNC6HwTZ0fieZa+rB0TUh8QP61Ebw0YEhv3NHqqzzCoGIv652xfDmxsyD9/U1utlcaQgoMPaiREUFaDRPe89eBn5zNWHSZTSPe1vhK0Sa2NbpwF3PFAwV11rGHggN2FpeDKbNZktMo3CIcvIZMMXvmCVCnmj4Ppfs7Jdq6qVOUqJZV+QAWClc0jqwfb9l4Jk7yvFfOFFOOGeDBCdAl/UmiNBnfNX4zCt3JgC8hDu4vNVGc6RCvcdli0wL8Eu+m0SGxIWCNoyZnw/pgMm1VOLwLfdVjeHZ/sQ6zQMfNliAJy5j/yoSJjgCmybCo5gLTW0kELZhhTMUkH+zMq8l3XM3HUFL4mvUlct1PfpwN6OcI5PD+QW5Di0QbQWXZFvDf0eiA/pcqb/Gmxb97eOmsqrQkd4/xUnXPKZCS4rLw6+nIq9BaKVOyVTNaaWIP93kbwehydnndDbPg19QQpkKEb7XCWNY6l8vuOK+FRY5h4IkDXJUyECg6/OOH67cbjlHCoRMeqA=',

        'CSDPassword' => 'JRLCD2208',

        'aqua_logo' => ''
         );



			var $api_data_reference_sandbox = array(
        # SANDBOX
        'api'      => 'https://testapi.facturoporti.com.mx',
        'username' => 'PruebasTimbrado',
        'password' => '@Notiene1',
        'timezone' => '@Notiene1',
        'local_save_folder'=> 'aqua-facturacion.ayaladigitalllc.com',

        'RFC'      => 'ACC220922GT0',
        'NombreRazonSocial' => 'AQUA CAR CLUB',
        'RegimenFiscal' => '601',
        'CodigoPostal' => '32030',

        'CSD' => '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',

        'CSDKey' => 'MIIFDjBABgkqhkiG9w0BBQ0wMzAbBgkqhkiG9w0BBQwwDgQIAgEAAoIBAQACAggAMBQGCCqGSIb3DQMHBAgwggS+AgEAMASCBMg98eLiNgZaraMlvjKTWXDfjSQJHVGeL1zZU3Ug6kit7iT6LcatjyBIQwPod+hfAcLIsB9MJoZpz9W+Ud/CldQB922K8qBn98Ar6uvtQqeCjKaYiXTccWl2LgB46HSt25LpsUu4JTyecLNcBlvZGS5luBbE+0ZdI8IoIgtYXo5E4SnBT9B6+8Y08P0niyRQ/M1yPjnRfsKqX/iSK9Pym6xoliquucR4rLwNh1p00ohZ7QXDUHOE56I2FIGV1UDVNPWMvyAoKzM5iBgv8Ib519AjArMznMJFHXHOipyqUYOFxI7Mu+J4NifweKimZnT0Bi41/3DuATwHVSkQMv2BhHEvSWLRHqSrW/fDqN4AAZq9kTBiPdOZL7PIWo19EtseGnXdv4OJiEuo6yF8Trxy/E/OdElSfjvMAyou4siqQ0ML1CSuilExHQ0vEIngDJj85MNy8epqPhnkOTLEapaJuKIQWyDfrOrotzebN6LxSv3Czmaybjv8f6Y8IurcId0Nnr47SpQDkr/2T8k//5oHdV7Nf3uhe4SPBLuNn5WZOcASGTDBaR6MXGHwafV4frR5hk+5VmOO8rXpo0LMgkv18fca8PPB5CZtGCksxOaRqPpF+0Cx4kY3k1OswyAJoBC/mbsed26duCztMa/i/IkCobqOtzFIdZ+X+WWH4gE1VLwUwUGdmOdWNFg0OjNsVrsabBKyQ0vqLtHZmvTvx5Sqct6PuLcwrNUvXno1z3GCbXXMrt1LOtw8cjYfwn9xrd27t4KcN8ECJLGWmb1BH4s96Zp/FWhjaHBvq6sRoV56PwkZ6bn3VJb6hSsRNeL01SwaBVLB90VeAzp67zJl8w0xR1XKxRiJonDW+60CsYGQmpH2LtqRheq0JJ2f9yH/2Pu4NxLTNRDGEJnJ+f1ZGjoDkVBUSV9g7n6GXZeBmUGAFQ19nO0fY0G/YAPridcxDOQc8JQP8DZyaBwOTfFVNgbl0Tcy0/U88GFsDKVj8E3sH5fIxzaH6LlM1hSQzIuYpD7rHHUa/Js7P4UbxhWGoS9gwWQL8I5HU10pBo2GqKaps4+P/GSBCntAJiL8XYSNrBJAPDkmqUimGtFJXvph23eLz6NPk5F9Oy3DNC6HwTZ0fieZa+rB0TUh8QP61Ebw0YEhv3NHqqzzCoGIv652xfDmxsyD9/U1utlcaQgoMPaiREUFaDRPe89eBn5zNWHSZTSPe1vhK0Sa2NbpwF3PFAwV11rGHggN2FpeDKbNZktMo3CIcvIZMMXvmCVCnmj4Ppfs7Jdq6qVOUqJZV+QAWClc0jqwfb9l4Jk7yvFfOFFOOGeDBCdAl/UmiNBnfNX4zCt3JgC8hDu4vNVGc6RCvcdli0wL8Eu+m0SGxIWCNoyZnw/pgMm1VOLwLfdVjeHZ/sQ6zQMfNliAJy5j/yoSJjgCmybCo5gLTW0kELZhhTMUkH+zMq8l3XM3HUFL4mvUlct1PfpwN6OcI5PD+QW5Di0QbQWXZFvDf0eiA/pcqb/Gmxb97eOmsqrQkd4/xUnXPKZCS4rLw6+nIq9BaKVOyVTNaaWIP93kbwehydnndDbPg19QQpkKEb7XCWNY6l8vuOK+FRY5h4IkDXJUyECg6/OOH67cbjlHCoRMeqA=',

        'CSDPassword' => 'JRLCD2208',

        'aqua_logo' => ''
         );

	  
}