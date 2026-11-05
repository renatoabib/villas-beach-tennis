<ul class="pc-navbar">

  {{-- Dashboard for all roles --}}
  @can('view dashboard')
    <li class="pc-item">
    <a href="{{ route('dashboard') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-gauge"></i></span>
      <span class="pc-mtext">Painel</span>
    </a>
    </li>
  @endcan

  {{-- QR Scanner --}}
  @hasanyrole('admin|superadmin|board')
    <li class="pc-item">
    <a href="{{ route('admin.qr-scanner') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-qrcode"></i></span>
      <span class="pc-mtext">Escanear Sócio</span>
    </a>
    </li>
  @endhasanyrole

  {{-- Statistics --}}
  @can('view statistics')
    <li class="pc-item">
    <a href="{{ route('statistics') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-chart-bar"></i></span>
      <span class="pc-mtext">Estatísticas</span>
    </a>
    </li>
  @endcan


  {{-- Roles Management --}}
  @can('manage roles')
    <li class="pc-item">
    <a href="{{ route('admin.roles.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-shield-star"></i></span>
      <span class="pc-mtext">Cargos</span>
    </a>
    </li>
  @endcan

  {{-- Permissions --}}
  @can('manage permissions')
    <li class="pc-item">
    <a href="{{ route('admin.permissions.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-keyhole"></i></span>
      <span class="pc-mtext">Permissões</span>
    </a>
    </li>
  @endcan

  {{-- Associations --}}
  @can('manage associations')
    <li class="pc-item">
    <a href="{{ route('admin.associations.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-users-three"></i></span>
      <span class="pc-mtext">Clubes</span>
    </a>
    </li>
  @endcan

  {{-- Membres --}}
  @can('view members')
    <li class="pc-item">
    <a href="{{ route('admin.membres.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-user-circle"></i></span>
      <span class="pc-mtext">Sócios</span>
    </a>
    </li>
  @endcan

  {{-- Meetings --}}
  @can('view meetings')
    <li class="pc-item">
    <a href="{{ route('admin.meetings.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-calendar-blank"></i></span>
      <span class="pc-mtext">Reuniões</span>
    </a>
    </li>
  @endcan

  {{-- Events --}}
  @can('view events')
    <li class="pc-item">
    <a href="{{ auth()->user()->hasRole('member') ? route('membre.events.index') : route('admin.events.index') }}"
      class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-calendar-check"></i></span>
      <span class="pc-mtext">Eventos</span>
    </a>
    </li>
  @endcan

  {{-- Mensalidades --}}
  @role('member')
    <li class="pc-item">
    <a href="{{ route('membre.memberships.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-currency-circle-dollar"></i></span>
      <span class="pc-mtext">Minhas Mensalidades</span>
    </a>
    </li>
  @endrole

  @hasanyrole('admin|superadmin|board')
    <li class="pc-item">
    <a href="{{ route('admin.memberships.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-currency-circle-dollar"></i></span>
      <span class="pc-mtext">Mensalidades</span>
    </a>
    </li>
  @endhasanyrole

  {{-- Aportes --}}
  @can('view cotisations')
    @role('member')
    <li class="pc-item">
    <a href="{{ route('membre.cotisations.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-receipt"></i></span>
      <span class="pc-mtext">Aportes</span>
    </a>
    </li>
    @endrole

    @hasanyrole('admin|superadmin|board')
    <li class="pc-item">
    <a href="{{ route('admin.cotisations.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-receipt"></i></span>
      <span class="pc-mtext">Aportes</span>
    </a>
    </li>
    @endhasanyrole
  @endcan


  {{-- Diárias --}}
  @can('view contributions')
    <li class="pc-item">
    <a href="{{ route('admin.contributions.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-hand-heart"></i></span>
      <span class="pc-mtext">Diárias</span>
    </a>
    </li>
  @endcan

  {{-- Despesas --}}
  @can('view expenses')
    <li class="pc-item">
    <a href="{{ route('admin.expenses.index') }}" class="pc-link">
      <span class="pc-micon"><i class="ph-duotone ph-currency-circle-dollar"></i></span>
      <span class="pc-mtext">Despesas</span>
    </a>
    </li>
  @endcan

</ul>
