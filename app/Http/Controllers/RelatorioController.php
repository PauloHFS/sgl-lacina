<?php

namespace App\Http\Controllers;

use App\Mail\ParticipacaoLacinaReportMail;
use App\Models\HistoricoUsuarioProjeto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function enviarRelatorioParticipacao(Request $request)
    {
        $user = Auth::user();
        $historico = HistoricoUsuarioProjeto::with('projeto')
            ->where('usuario_id', $user->id)
            ->orderBy('data_inicio', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdfs.relatorio_participacao', [
            'user' => $user,
            'historico' => $historico,
        ]);

        Mail::to($user->email)->send(new ParticipacaoLacinaReportMail($user, $historico, $pdf->output(), 'relatorio_participacao_' . $user->id . '_' . now()->format('YmdHis') . '.pdf'));

        return back()->with('success', 'Relatório de participação enviado para o seu e-mail com o PDF anexado.');
    }
}
