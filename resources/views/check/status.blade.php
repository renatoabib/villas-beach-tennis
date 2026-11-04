<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status do Sócio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5 text-center">
        <div class="card shadow">
            <div class="card-body">
                <h3>Sócio: {{ $user->name }}</h3>
                <p class="text-muted">{{ $user->member_number }}</p>
                <div class="alert {{ $financial['can_play'] ? 'alert-success' : 'alert-danger' }} fs-4">
                    {{ $financial['label'] }}
                </div>
                <p>{{ $financial['message'] }}</p>
            </div>
        </div>
    </div>
</body>
</html>
