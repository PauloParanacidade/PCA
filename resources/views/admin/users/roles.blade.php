@extends('adminlte::page')

@section('title', 'Gerenciamento de Permissões')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Gerenciamento de Permissões</h1>
        </div>
    </div>
@stop

@section('content')
    <style>
        .pagination {
            margin: 0;
            justify-content: center;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        .pagination .page-link {
            color: #007bff;
        }

        .pagination .page-item.active .page-link:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .pagination .page-link:hover {
            color: #0056b3;
        }
    </style>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('admin.users.roles') }}" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ $search }}" class="form-control"
                                placeholder="Buscar por nome ou email">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                @if ($search)
                                    <a href="{{ route('admin.users.roles') }}" class="btn btn-default">
                                        <i class="fas fa-times"></i> Limpar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group">
                        <button type="button" onclick="openCreateUserModal()" class="btn btn-purple">
                            <i class="fas fa-user-plus"></i> Criar Usuário Externo
                        </button>
                        <button type="button" onclick="openMassEditModal()" class="btn btn-success">
                            <i class="fas fa-users-cog"></i> Edição em Massa
                        </button>
                        <form action="{{ route('admin.users.sync-ldap') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-sync"></i> Sincronizar LDAP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 40px">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="selectAll" class="custom-control-input">
                                    <label for="selectAll" class="custom-control-label"></label>
                                </div>
                            </th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th style="width: 100px">Status</th>
                            <th>Permissões</th>
                            <th style="width: 280px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" form="massEditForm" name="user_ids[]"
                                            value="{{ $user->id }}" class="custom-control-input user-checkbox"
                                            id="user-{{ $user->id }}">
                                        <label for="user-{{ $user->id }}" class="custom-control-label"></label>
                                    </div>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $user->active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $user->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-info mr-1">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button onclick="openModal('modal-{{ $user->id }}')"
                                            class="btn btn-warning btn-sm" title="Editar Permissões">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if (!$user->hasRole('admin'))
                                            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $user->active ? 'btn-danger' : 'btn-success' }}"
                                                    title="{{ $user->active ? 'Desativar Usuário' : 'Ativar Usuário' }}">
                                                    <i
                                                        class="fas {{ $user->active ? 'fa-user-times' : 'fa-user-check' }}"></i>
                                                </button>
                                            </form>
                                            @if ($user->isExterno)
                                                <button type="button"
                                                    onclick="openDeleteModal('{{ $user->id }}', '{{ $user->name }}')"
                                                    class="btn btn-danger btn-sm" title="Excluir Usuário">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                            <form action="{{ route('admin.impersonate', $user) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-sm"
                                                    title="Impersonar Usuário">
                                                    <i class="fas fa-user-secret"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $users->appends(['search' => $search])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Formulário de Edição em Massa -->
    <form id="massEditForm" action="{{ route('admin.users.roles.mass-update') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Modais Individuais -->
    @foreach ($users as $user)
        <div id="modal-{{ $user->id }}" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Permissões - {{ $user->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.users.roles.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            @foreach ($roles as $role)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="role-{{ $user->id }}-{{ $role->id }}"
                                        name="roles[]" value="{{ $role->id }}"
                                        {{ $user->roles->contains($role->id) ? 'checked' : '' }}
                                        class="custom-control-input">
                                    <label for="role-{{ $user->id }}-{{ $role->id }}"
                                        class="custom-control-label">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal de Edição em Massa -->
    <div id="massEditModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edição em Massa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Selecione as permissões que deseja aplicar aos usuários selecionados:</p>
                    @foreach ($roles as $role)
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" id="mass-role-{{ $role->id }}" name="mass_roles[]"
                                value="{{ $role->id }}" class="custom-control-input">
                            <label for="mass-role-{{ $role->id }}" class="custom-control-label">
                                {{ $role->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="massEditForm" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Criação de Usuário Externo -->
    <div id="createUserModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Criar Usuário Externo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nome</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                            <div id="email-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label for="password">Senha</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Senha</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control" required>
                            <div id="password-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label>Permissões</label>
                            <div class="mt-2">
                                @foreach ($roles as $role)
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="new-user-role-{{ $role->id }}" name="roles[]"
                                            value="{{ $role->id }}" class="custom-control-input">
                                        <label for="new-user-role-{{ $role->id }}" class="custom-control-label">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário <span id="deleteUserName"
                            class="font-weight-bold"></span>?</p>
                    <p class="text-danger">Esta ação não pode ser desfeita.</p>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        .btn-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-group .btn {
            margin: 0;
            padding: 0.375rem 0.75rem;
            min-width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-group .btn i {
            margin: 0;
            font-size: 1rem;
        }

        .btn-group .btn-sm {
            padding: 0.25rem 0.5rem;
            min-width: 32px;
            height: 32px;
        }

        .btn-group .btn-sm i {
            font-size: 0.875rem;
        }

        .table td {
            vertical-align: middle;
        }

        .custom-control {
            margin: 0;
        }

        .modal-body .custom-control {
            margin-bottom: 0.5rem;
        }

        .card-header .btn-group {
            margin-left: auto;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }

        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: #fff;
        }

        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }

        .table th {
            background-color: #f8f9fa;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .card-header .btn-group .btn {
            min-width: auto;
            padding: 0.375rem 1rem;
        }

        .card-header .btn-group .btn i {
            margin-right: 0.5rem;
        }

        .input-group .btn {
            min-width: auto;
            height: 38px;
        }

        .modal-footer .btn {
            min-width: 100px;
        }

        .table .btn-group {
            justify-content: center;
        }

        .table .btn-group .btn {
            padding: 0.25rem 0.5rem;
            width: 44px;
            min-width: 44px;
            max-width: 44px;
            justify-content: center;
        }

        .table .btn-group .btn i {
            font-size: 0.875rem;
        }
    </style>
@stop

@section('js')
    <script>
        function openModal(modalId) {
            $('#' + modalId).modal('show');
        }

        function closeModal(modalId) {
            $('#' + modalId).modal('hide');
        }

        function openMassEditModal() {
            const selectedUsers = document.querySelectorAll('input.user-checkbox:checked');
            if (selectedUsers.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Selecione pelo menos um usuário para edição em massa.'
                });
                return;
            }
            $('#massEditModal').modal('show');
        }

        function openCreateUserModal() {
            $('#createUserModal').modal('show');
        }

        function openDeleteModal(userId, userName) {
            $('#deleteUserName').text(userName);
            $('#deleteForm').attr('action', `/admin/users/${userId}`);
            $('#deleteModal').modal('show');
        }

        // Validação de senha
        $('#password_confirmation').on('input', function() {
            const password = $('#password').val();
            const confirmation = $(this).val();
            const errorElement = $('#password-error');

            if (password !== confirmation) {
                errorElement.text('As senhas não coincidem');
                errorElement.show();
            } else {
                errorElement.hide();
            }
        });

        $('#password').on('input', function() {
            const confirmation = $('#password_confirmation').val();
            const errorElement = $('#password-error');

            if ($(this).val() !== confirmation && confirmation !== '') {
                errorElement.text('As senhas não coincidem');
                errorElement.show();
            } else {
                errorElement.hide();
            }
        });

        // Validação de email
        $('#email').on('blur', function() {
            const email = $(this).val();
            const errorElement = $('#email-error');

            if (email) {
                $.get(`/api/check-email?email=${encodeURIComponent(email)}`)
                    .done(function(data) {
                        if (data.exists) {
                            errorElement.text('Este e-mail já está em uso');
                            errorElement.show();
                        } else {
                            errorElement.hide();
                        }
                    })
                    .fail(function(error) {
                        console.error('Erro ao verificar email:', error);
                    });
            }
        });

        // Validação do formulário antes do envio
        $('form[action="{{ route('admin.users.store') }}"]').on('submit', function(e) {
            const password = $('#password').val();
            const confirmation = $('#password_confirmation').val();
            const email = $('#email').val();
            let hasError = false;

            if (password !== confirmation) {
                e.preventDefault();
                $('#password-error').text('As senhas não coincidem').show();
                hasError = true;
            }

            // Verificar email antes de enviar
            $.get(`/api/check-email?email=${encodeURIComponent(email)}`)
                .done(function(data) {
                    if (data.exists) {
                        e.preventDefault();
                        $('#email-error').text('Este e-mail já está em uso').show();
                        hasError = true;
                    }
                })
                .fail(function(error) {
                    console.error('Erro ao verificar email:', error);
                });
        });

        // Selecionar todos os checkboxes
        $('#selectAll').on('change', function() {
            $('.user-checkbox').prop('checked', $(this).prop('checked'));
        });
    </script>
@stop
