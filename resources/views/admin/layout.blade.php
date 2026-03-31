<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理後台</title>
    <style>
        :root {
            --primary: #2d3748;
            --secondary: #e2e8f0;
            --accent: #48bb78;
            --text-dark: #1a202c;
            --text-light: #718096;
            --white: #ffffff;
            --bg: #f7fafc;
        }

        body {
            margin: 0;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary);
            color: var(--white);
            display: flex;
            flex-direction: column;
            padding: 2rem 1rem;
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 1rem;
        }

        .sidebar a {
            color: var(--secondary);
            text-decoration: none;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(255,255,255,0.1);
            color: var(--white);
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            background: var(--accent);
            color: var(--white);
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #38a169;
        }

        .btn-danger { background: #e53e3e; }
        .btn-danger:hover { background: #c53030; }

        .btn-secondary { background: var(--text-light); }
        .btn-secondary:hover { background: #4a5568; }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert-success { background: #c6f6d5; color: #22543d; }
        .alert-danger { background: #fed7d7; color: #822727; }

        .card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--secondary);
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid var(--secondary);
        }
        table th {
            background-color: var(--bg);
            color: var(--text-light);
        }
        .img-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>Logo Admin</h2>
        <a href="{{ route('admin.products.index') }}" class="active">商品管理</a>
        <a href="{{ route('shop.index') }}" target="_blank">查看前台</a>
    </aside>

    <main class="main-content">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                請檢查輸入的內容。
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
