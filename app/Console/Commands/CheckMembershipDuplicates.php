<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class CheckMembershipDuplicates extends Command
{
    protected $signature   = 'membership:check-duplicates {--test : Envía correo de prueba sin necesitar duplicados reales}';
    protected $description = 'Detecta duplicados exactos en client_membership y alerta por correo si los hay';

    public function handle()
    {
        if ($this->option('test')) {
            $duplicados = [
                (object)['_id' => '6a0e18fa56201def47ada2f5', 'start_date' => '2026-05-20 00:00:00', 'end_date' => '2026-06-20 23:59:59', 'total' => 2, 'id_conservar' => 9123],
                (object)['_id' => '69e64bde19f058135be9363a', 'start_date' => '2026-04-20 00:00:00', 'end_date' => '2026-05-20 23:59:59', 'total' => 2, 'id_conservar' => 8195],
            ];
            $this->warn('[PRUEBA] Enviando correo de prueba con datos simulados...');
            $destinatarios = explode(',', env('SYNC_ALERT_EMAILS', ''));
            $destinatarios = array_filter(array_map('trim', $destinatarios));
            $enviado = $this->enviarCorreo($duplicados, 2, 2, $destinatarios);
            $this->info($enviado ? 'Correo de prueba enviado a: ' . implode(', ', $destinatarios) : 'Error al enviar.');
            return 0;
        }

        $duplicados = DB::select("
            SELECT cm._id, cm.start_date, cm.end_date, COUNT(*) AS total,
                   MIN(cm.id) AS id_conservar
            FROM client_membership cm
            WHERE cm.deleted_at IS NULL
            GROUP BY cm._id, cm.start_date, cm.end_date
            HAVING COUNT(*) > 1
            ORDER BY total DESC
            LIMIT 50
        ");

        $total_grupos    = count($duplicados);
        $total_sobrantes = array_sum(array_map(fn($r) => $r->total - 1, $duplicados));

        if ($total_grupos === 0) {
            $this->info('Sin duplicados en client_membership. Todo limpio.');
            return 0;
        }

        $this->warn("Se encontraron {$total_grupos} grupos duplicados ({$total_sobrantes} registros sobrantes).");

        $destinatarios = explode(',', env('SYNC_ALERT_EMAILS', ''));
        $destinatarios = array_filter(array_map('trim', $destinatarios));

        if (empty($destinatarios)) {
            $this->warn('SYNC_ALERT_EMAILS no está configurado en .env — no se envió correo.');
            return 1;
        }

        $enviado = $this->enviarCorreo($duplicados, $total_grupos, $total_sobrantes, $destinatarios);

        if ($enviado) {
            $this->info('Alerta enviada a: ' . implode(', ', $destinatarios));
        } else {
            $this->error('Error al enviar el correo. Revisa el log.');
        }

        $this->table(
            ['_id', 'start_date', 'end_date', 'Copias', 'ID a conservar'],
            array_map(fn($r) => [$r->_id, $r->start_date, $r->end_date, $r->total, $r->id_conservar], $duplicados)
        );

        return 0;
    }

    private function enviarCorreo(array $duplicados, int $grupos, int $sobrantes, array $destinatarios): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->Username   = 'no-reply@totalgas.com';
            $mail->Password   = 'sysdhepknmlkigbs';

            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isHTML(true);
            $mail->setLanguage('es');

            $mail->setFrom('no-reply@totalgas.com', 'AquaStaff | Monitor de Membresías');

            foreach ($destinatarios as $email) {
                $mail->addAddress($email);
            }

            $mail->Subject = '[AquaStaff] Duplicados en membresías — ' . now()->format('d/m/Y');
            $mail->Body    = $this->buildHtml($duplicados, $grupos, $sobrantes);
            $mail->AltBody = $this->buildPlainText($duplicados, $grupos, $sobrantes);

            return $mail->send();

        } catch (Exception $e) {
            error_log('MembershipDuplicates mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function buildHtml(array $duplicados, int $grupos, int $sobrantes): string
    {
        $filas = '';
        foreach ($duplicados as $r) {
            $filas .= "
                <tr>
                    <td style='padding:8px 12px;border-bottom:1px solid #eee;font-family:monospace;font-size:12px;'>{$r->_id}</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>{$r->start_date}</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>{$r->end_date}</td>
                    <td style='padding:8px 12px;border-bottom:1px solid #eee;text-align:center;'>
                        <span style='background:#e74c3c;color:#fff;padding:2px 8px;border-radius:10px;font-size:12px;'>{$r->total}</span>
                    </td>
                    <td style='padding:8px 12px;border-bottom:1px solid #eee;text-align:center;'>{$r->id_conservar}</td>
                </tr>";
        }

        $fecha = now()->format('d/m/Y H:i');
        $nota  = count($duplicados) === 50 ? '<p style="color:#e67e22;font-size:13px;">⚠️ Se muestran solo los primeros 50 grupos. Puede haber más.</p>' : '';

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head><meta charset='UTF-8'></head>
        <body style='font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px;'>
        <div style='max-width:700px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);'>
            <div style='background:#e67e22;color:#fff;padding:24px 30px;'>
                <h1 style='margin:0;font-size:20px;'>⚠️ Duplicados en Membresías</h1>
                <p style='margin:6px 0 0;font-size:13px;opacity:.85;'>{$fecha} — AquaStaff</p>
            </div>
            <div style='padding:24px 30px;'>
                <p style='color:#444;font-size:14px;'>
                    Se detectaron <strong>{$grupos} grupos duplicados</strong> con un total de
                    <strong>{$sobrantes} registros sobrantes</strong> en <code>client_membership</code>.
                </p>
                {$nota}
                <table style='width:100%;border-collapse:collapse;font-size:13px;margin-top:16px;'>
                    <thead>
                        <tr>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:left;'>_id (MongoDB)</th>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:left;'>Inicio</th>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:left;'>Vence</th>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:center;'>Copias</th>
                            <th style='background:#2c3e50;color:#fff;padding:10px 12px;text-align:center;'>ID a conservar</th>
                        </tr>
                    </thead>
                    <tbody>{$filas}</tbody>
                </table>
                <p style='margin-top:20px;font-size:13px;color:#666;'>
                    Ejecuta <code>php artisan membership:check-duplicates</code> para ver el detalle completo,
                    o aplica el UPDATE de soft-delete desde la consola.
                </p>
            </div>
            <div style='padding:16px 30px;background:#f9f9f9;font-size:12px;color:#999;text-align:center;border-top:1px solid #eee;'>
                Correo generado automáticamente por AquaStaff. No responder.
            </div>
        </div>
        </body></html>";
    }

    private function buildPlainText(array $duplicados, int $grupos, int $sobrantes): string
    {
        $lines   = ['Alerta AquaStaff — Duplicados en Membresías — ' . now()->format('d/m/Y H:i'), ''];
        $lines[] = "Grupos duplicados: {$grupos} | Registros sobrantes: {$sobrantes}";
        $lines[] = str_repeat('-', 60);
        foreach ($duplicados as $r) {
            $lines[] = "- _id: {$r->_id} | {$r->start_date} → {$r->end_date} | Copias: {$r->total} | Conservar id: {$r->id_conservar}";
        }
        return implode("\n", $lines);
    }
}
