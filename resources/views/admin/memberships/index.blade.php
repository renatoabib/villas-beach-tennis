@extends('layouts.main')

@section('title', 'Gestão de Mensalidades')
@section('breadcrumb-item', 'Administração')
@section('breadcrumb-item-active', 'Mensalidades')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')
    @if(session('toast') || session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('favicon.svg') }}" class="img-fluid me-2" alt="favicon" style="width: 17px">
                    <strong class="me-auto">Villas Beach Tennis</strong>
                    <small>Agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('toast') ?? session('success') ?? session('error') }}
                </div>
            </div>
        </div>
    @endif

    <!-- Cards de Indicadores do Mês Atual -->
    <div class="row mb-3">
        <div class="col-md-4 col-sm-6">
            <div class="card bg-light-primary mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avtar avtar-s bg-primary text-white me-3">
                            <i class="ti ti-cash f-20"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Arrecadado neste Mês</h6>
                            <h3 class="mb-0 text-primary">R$ {{ number_format($totalPaidMonth, 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card bg-light-success mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avtar avtar-s bg-success text-white me-3">
                            <i class="ti ti-circle-check f-20"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Mensalidades Pagas</h6>
                            <h3 class="mb-0 text-success">{{ $countPaidMonth }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card bg-light-warning mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avtar avtar-s bg-warning text-white me-3">
                            <i class="ti ti-clock f-20"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Pendentes / Em Aberto</h6>
                            <h3 class="mb-0 text-warning">{{ $countPendingMonth }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela e Filtros -->
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header d-sm-flex align-items-center justify-content-between">
                    <h5 class="mb-3 mb-sm-0">Histórico de Mensalidades</h5>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.memberships.generate-pending') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary" title="Gerar pendências para usuários ativos">
                                <i class="ti ti-refresh me-1"></i> Gerar Pendências
                            </button>
                        </form>
                        <a href="{{ route('admin.memberships.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Lançar Pagamento
                        </a>
                    </div>
                </div>

                <!-- Formulário de Filtro -->
                <div class="card-body border-bottom py-3">
                    <form method="GET" action="{{ route('admin.memberships.index') }}" class="row g-2 align-items-center">
                        <div class="col-md-4 col-sm-6">
                            <select name="user_id" class="form-select form-select-sm">
                                <option value="">Todos os Sócios</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">Todos os Status</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago / Adimplente</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Em Atraso</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}" placeholder="Mês (AAAA-MM)">
                        </div>
                        <div class="col-md-2 col-sm-6 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-secondary w-100">
                                <i class="ti ti-filter"></i> Filtrar
                            </button>
                            @if(request()->anyFilled(['user_id', 'status', 'month']))
                                <a href="{{ route('admin.memberships.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpar Filtros">
                                    <i class="ti ti-x"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Sócio</th>
                                    <th>Mês Ref.</th>
                                    <th>Vencimento</th>
                                    <th>Valor</th>
                                    <th>Forma</th>
                                    <th>Comprovante</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($memberships as $m)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avtar avtar-xs bg-light-primary rounded-circle me-2">
                                                    {{ strtoupper(substr($m->user->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $m->user->name ?? '—' }}</h6>
                                                    <small class="text-muted">{{ $m->user->phone ?? $m->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $m->month_year_formatted }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $m->due_date ? $m->due_date->format('d/m/Y') : 'Dia 05' }}</small>
                                        </td>
                                        <td>
                                            <strong>R$ {{ number_format($m->amount, 2, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-secondary">{{ strtoupper($m->payment_method ?? 'PIX') }}</span>
                                        </td>
                                        <td>
                                            @if($m->payment_proof)
                                                <a href="{{ asset('storage/' . $m->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2" title="Visualizar Comprovante">
                                                    <i class="ti ti-file-text"></i> Ver Anexo
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="{{ $m->status_badge_class }}">
                                                {{ $m->status_label }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($m->status !== \App\Models\Membership::STATUS_PAID)
                                                <form action="{{ route('admin.memberships.approve', $m) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success py-1 px-2" title="Marcar como Pago">
                                                        <i class="ti ti-check"></i> Aprovar
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.memberships.edit', $m) }}" class="avtar avtar-xs btn-link-secondary" title="Editar">
                                                <i class="ti ti-edit f-18"></i>
                                            </a>
                                            <form action="{{ route('admin.memberships.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta mensalidade?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="avtar avtar-xs btn-link-danger border-0 bg-transparent" title="Excluir">
                                                    <i class="ti ti-trash f-18"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            Nenhum registro de mensalidade encontrado com os filtros atuais.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end p-3">
                        {{ $memberships->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
