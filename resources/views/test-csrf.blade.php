<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Тест CSRF</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .result { margin-top: 20px; padding: 10px; border: 1px solid #ccc; }
        .success { background: #d4edda; }
        .error { background: #f8d7da; }
    </style>
</head>
<body>
    <h1>Тест CSRF токена</h1>
    
    <div>
        <p>CSRF токен из мета-тега: <code id="csrf-token">{{ csrf_token() }}</code></p>
        <p>Сессия ID: <code>{{ session()->getId() }}</code></p>
    </div>
    
    <button id="test-btn">Тест AJAX запроса</button>
    
    <div id="result" class="result"></div>
    
    <script>
    $(document).ready(function() {
        // Способ 1: Использовать $.ajaxSetup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        $('#test-btn').click(function() {
            $('#result').html('Отправка запроса...').removeClass('success error');
            
            // Способ 2: Передавать в data
            $.ajax({
                url: '/test-csrf',
                method: 'POST',
                data: {
                    test: 'данные',
                    _token: $('meta[name="csrf-token"]').attr('content') // дублируем для надежности
                },
                success: function(response) {
                    $('#result').addClass('success').html(
                        'Успех!<br>' + 
                        JSON.stringify(response, null, 2)
                    );
                },
                error: function(xhr) {
                    $('#result').addClass('error').html(
                        'Ошибка ' + xhr.status + '<br>' +
                        JSON.stringify(xhr.responseJSON, null, 2)
                    );
                }
            });
        });
    });
    </script>
</body>
</html>