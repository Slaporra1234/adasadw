<?php
session_start();
require_once 'conexao.php';

$msg = '';
$msg_type = '';

// Verificar token válido
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare('SELECT * FROM reset_tokens WHERE token = ? AND usado = 0 AND expires_at > NOW()');
    $stmt->execute([$token]);
    $token_data = $stmt->fetch();
    
    if (!$token_data) {
        $msg = 'Link inválido ou expirado. Solicite um novo link.';
        $msg_type = 'error';
        $invalid_token = true;
    }
} else {
    $msg = 'Link inválido. Solicite um novo link.';
    $msg_type = 'error';
    $invalid_token = true;
}

// Processar redefinição de senha
if (isset($_POST['reset_password']) && isset($token_data)) {
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    if ($senha !== $confirmar_senha) {
        $msg = 'As senhas não coincidem!';
        $msg_type = 'error';
    } else {
        // Atualizar senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
        $stmt->execute([$senha_hash, $token_data['user_id']]);
        
        // Marcar token como usado
        $stmt = $pdo->prepare('UPDATE reset_tokens SET usado = 1 WHERE id = ?');
        $stmt->execute([$token_data['id']]);
        
        $msg = 'Senha redefinida com sucesso! Faça login com sua nova senha.';
        $msg_type = 'success';
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha - MarketPlace Brasil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts & FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
/* (O mesmo CSS do arquivo esqueci_senha.php) */
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --primary-light: #3b82f6;
    --primary-lighter: #dbeafe;
    --secondary: #1e293b;
    --secondary-light: #475569;
    --accent: #f59e0b;
    --background: #f8fafc;
    --surface: #ffffff;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --border-light: #f1f5f9;
    --success: #059669;
    --success-light: #d1fae5;
    --danger: #dc2626;
    --danger-light: #fef2f2;
    --warning: #d97706;
    --warning-light: #fef3c7;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --radius-sm: 0.375rem;
    --radius: 0.5rem;
    --radius-md: 0.75rem;
    --radius-lg: 1rem;
    --radius-xl: 1.5rem;
    --transition: all 0.15s ease-in-out;
    --transition-slow: all 0.3s ease-in-out;
}

* {
    box-sizing: border-box;
}

html, body {
    height: 100%;
    min-height: 100vh;
    margin: 0;
    padding: 0;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: var(--text-primary);
    line-height: 1.6;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    overflow-x: hidden;
}

.main-card {
    background: var(--surface);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    max-width: 420px;
    width: 100%;
    padding: 2.5rem 2rem 2rem;
    position: relative;
    border: 1px solid var(--border-light);
    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(2rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.voltar-btn {
    position: absolute;
    left: 1.5rem;
    top: 1.5rem;
    background: var(--surface);
    color: var(--primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
    z-index: 10;
    box-shadow: var(--shadow-sm);
}

.voltar-btn:hover {
    background: var(--primary);
    color: var(--surface);
    border-color: var(--primary);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.voltar-btn i {
    font-size: 0.75rem;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-top: 1rem;
}

.auth-header .logo {
    width: 3.5rem;
    height: 3.5rem;
    background: var(--primary-lighter);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: var(--primary);
    font-size: 1.5rem;
}

.auth-header h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
    letter-spacing: -0.025em;
}

.auth-header p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin: 0;
    font-weight: 400;
}

.msg {
    padding: 0.875rem 1rem;
    border-radius: var(--radius);
    text-align: center;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
    border: 1px solid transparent;
}

.msg.error {
    background: var(--danger-light);
    color: var(--danger);
    border-color: #fecaca;
}

.msg.success {
    background: var(--success-light);
    color: var(--success);
    border-color: #a7f3d0;
}

form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-left: 0.125rem;
}

.input-wrap {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--background);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    transition: var(--transition);
    min-height: 3rem;
}

.input-wrap:focus-within {
    border-color: var(--primary);
    background: var(--surface);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.input-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    margin-left: 0.75rem;
    border-radius: var(--radius);
    background: var(--primary-lighter);
    color: var(--primary);
    font-size: 1rem;
    flex-shrink: 0;
}

.form-group input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.9375rem;
    color: var(--text-primary);
    padding: 0.75rem 0.75rem 0.75rem 0.5rem;
    font-family: inherit;
    font-weight: 400;
}

.form-group input::placeholder {
    color: var(--text-muted);
}

.auth-btn {
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: var(--radius-md);
    background: var(--primary);
    color: var(--surface);
    font-weight: 600;
    font-size: 0.9375rem;
    letter-spacing: 0.025em;
    cursor: pointer;
    transition: var(--transition);
    margin-top: 0.5rem;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}

.auth-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.auth-btn:hover::before {
    left: 100%;
}

.auth-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.auth-btn:active {
    transform: translateY(0);
    box-shadow: var(--shadow-sm);
}

.switch {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-light);
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.switch a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.switch a:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 640px) {
    body {
        padding: 0.5rem;
        align-items: flex-start;
        padding-top: 2rem;
    }
    
    .main-card {
        max-width: 100%;
        padding: 2rem 1.5rem 1.5rem;
        margin: 0;
    }
    
    .voltar-btn {
        left: 1rem;
        top: 1rem;
        font-size: 0.8125rem;
        padding: 0.5rem 0.875rem;
    }
    
    .auth-header h2 {
        font-size: 1.5rem;
    }
    
    .auth-header p {
        font-size: 0.8125rem;
    }
    
    .form-group input {
        font-size: 0.875rem;
    }
}

@media (max-width: 480px) {
    .main-card {
        padding: 1.5rem 1rem 1rem;
    }
    
    .auth-header {
        margin-bottom: 1.5rem;
    }
    
    .auth-header .logo {
        width: 3rem;
        height: 3rem;
        font-size: 1.25rem;
    }
    
    .voltar-btn {
        left: 0.75rem;
        top: 0.75rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }
}

/* Loading animation for form submission */
.auth-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.auth-btn:disabled::after {
    content: '';
    position: absolute;
    width: 1rem;
    height: 1rem;
    border: 2px solid transparent;
    border-top: 2px solid var(--surface);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* Focus styles for accessibility */
.auth-btn:focus-visible,
.voltar-btn:focus-visible,
.switch a:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}
    </style>
</head>
<body>
    <div class="main-card">
        <a class="voltar-btn" href="login_cadastro.php"><i class="fas fa-arrow-left"></i>Voltar</a>
        <div class="auth-header">
            <div class="logo"><i class="fas fa-key"></i></div>
            <h2>Redefinir Senha</h2>
            <p>Crie uma nova senha para sua conta</p>
        </div>
        <?php if (!empty($msg)): ?>
            <div class="msg <?= isset($msg_type) ? $msg_type : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        
        <?php if (isset($invalid_token)): ?>
            <div class="switch">
                <a href="esqueci_senha.php">Solicitar novo link</a>
            </div>
        <?php elseif (isset($success)): ?>
            <div class="switch">
                <a href="login_cadastro.php">Ir para login</a>
            </div>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label for="senha">Nova Senha</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="senha" name="senha" placeholder="Digite sua nova senha" required minlength="6">
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua nova senha" required minlength="6">
                    </div>
                </div>
                <button type="submit" name="reset_password" class="auth-btn">Redefinir Senha</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>