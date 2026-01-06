<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Jadwal Attendance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #666;
            line-height: 1.6;
        }

        .info-box strong {
            color: #333;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 30px;
        }

        button, .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
            transform: translateY(-2px);
        }

        .btn-block {
            grid-column: 1 / -1;
        }

        .separator {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 12px;
            align-items: center;
            margin: 30px 0;
        }

        .separator::before,
        .separator::after {
            content: '';
            height: 1px;
            background: #e0e0e0;
        }

        .separator span {
            text-align: center;
            color: #999;
            font-size: 12px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 24px;
            }

            .header h1 {
                font-size: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .button-group {
                grid-template-columns: 1fr;
            }
        }

        .preview-box {
            background: #f0f4ff;
            border: 1px solid #d0deff;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .preview-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
        }

        .preview-item strong {
            color: #333;
            flex: 1;
        }

        .preview-item .value {
            color: #667eea;
            font-weight: 600;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Settings Jadwal Attendance</h1>
            <p>Atur jadwal check-in dan check-out untuk semua guru</p>
        </div>

        @if ($errors->any())
            <div class="alert error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="alert success">
                ✓ {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            <div class="info-box">
                <strong>📌 Informasi Jadwal:</strong><br>
                • Check-in bisa dilakukan dari <span id="preview-check-in-start">06:30</span> - <span id="preview-check-in-end">07:30</span><br>
                • Check-out bisa dilakukan setelah <span id="preview-check-out">17:00</span>
            </div>

            <div class="preview-box">
                <div class="preview-item">
                    <strong>Jam Check-in</strong>
                    <span class="value" id="preview-ci-time">07:00</span>
                </div>
                <div class="preview-item">
                    <strong>Jam Check-out</strong>
                    <span class="value" id="preview-co-time">17:00</span>
                </div>
                <div class="preview-item">
                    <strong>Awal Check-in (menit sebelum)</strong>
                    <span class="value" id="preview-before">30 min</span>
                </div>
                <div class="preview-item">
                    <strong>Akhir Check-in (menit sesudah)</strong>
                    <span class="value" id="preview-after">30 min</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="check_in_time">🕐 Jam Check-in</label>
                    <input type="time" id="check_in_time" name="check_in_time" 
                           value="{{ $settings['check_in_time'] }}" required>
                </div>
                <div class="form-group">
                    <label for="check_out_time">🕑 Jam Check-out</label>
                    <input type="time" id="check_out_time" name="check_out_time" 
                           value="{{ $settings['check_out_time'] }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="check_in_before_minutes">📍 Mulai Check-in (menit sebelum)</label>
                    <input type="number" id="check_in_before_minutes" name="check_in_before_minutes" 
                           value="{{ $settings['check_in_before_minutes'] }}" min="0" max="120" required>
                </div>
                <div class="form-group">
                    <label for="check_in_after_minutes">📍 Akhir Check-in (menit sesudah)</label>
                    <input type="number" id="check_in_after_minutes" name="check_in_after_minutes" 
                           value="{{ $settings['check_in_after_minutes'] }}" min="0" max="120" required>
                </div>
            </div>

            <div class="separator">
                <span>Jadwal Compliance</span>
            </div>

            <div class="info-box">
                <strong>⚠️ Catatan:</strong><br>
                • Guru hanya bisa check-in dalam rentang waktu yang ditentukan<br>
                • Check-out bisa dilakukan kapan saja setelah jam yang ditentukan<br>
                • Perubahan settings berlaku otomatis untuk semua guru
            </div>

            <div class="button-group">
                <button type="reset" class="btn btn-secondary">Reset Form</button>
                <button type="submit" class="btn btn-primary btn-block">💾 Simpan Settings</button>
            </div>
        </form>
    </div>

    <script>
        const inputs = {
            checkInTime: document.getElementById('check_in_time'),
            checkOutTime: document.getElementById('check_out_time'),
            checkInBefore: document.getElementById('check_in_before_minutes'),
            checkInAfter: document.getElementById('check_in_after_minutes'),
        };

        function updatePreview() {
            const checkInTime = inputs.checkInTime.value;
            const checkOutTime = inputs.checkOutTime.value;
            const before = parseInt(inputs.checkInBefore.value) || 0;
            const after = parseInt(inputs.checkInAfter.value) || 0;

            if (checkInTime) {
                const [hours, minutes] = checkInTime.split(':').map(Number);
                const startTime = new Date();
                startTime.setHours(hours - Math.floor(before / 60), minutes - (before % 60));
                const endTime = new Date();
                endTime.setHours(hours + Math.floor(after / 60), minutes + (after % 60));

                document.getElementById('preview-ci-time').textContent = checkInTime;
                document.getElementById('preview-check-in-start').textContent = 
                    String(startTime.getHours()).padStart(2, '0') + ':' + 
                    String(startTime.getMinutes()).padStart(2, '0');
                document.getElementById('preview-check-in-end').textContent = 
                    String(endTime.getHours()).padStart(2, '0') + ':' + 
                    String(endTime.getMinutes()).padStart(2, '0');
                document.getElementById('preview-before').textContent = before + ' min';
                document.getElementById('preview-after').textContent = after + ' min';
            }

            if (checkOutTime) {
                document.getElementById('preview-co-time').textContent = checkOutTime;
                document.getElementById('preview-check-out').textContent = checkOutTime;
            }
        }

        Object.values(inputs).forEach(input => {
            input.addEventListener('change', updatePreview);
            input.addEventListener('input', updatePreview);
        });

        updatePreview();
    </script>
</body>
</html>
