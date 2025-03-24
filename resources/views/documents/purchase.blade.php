<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RCE - Pedido de Compra {{ $data['purchaseData']['id'] }}</title>
</head>
<body style="padding: 0 20px; font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
<table style="width: 100%; border-bottom: 5px solid #007BFF; padding-bottom: 10px;">
    <tr>
        <td style="width: 50%;">
            <img src="{{ public_path('assets/img/logo.jpg') }}" alt="Logo RCE" style="width: 150px;">
        </td>
        <td style="width: 50%; text-align: right; font-size: 18px; color: #555;">
            <strong>Sistema RCE</strong><br>
            Presidente Prudente, São Paulo<br>
            suporte@rce.com.br
        </td>
    </tr>
</table>

<table style="width: 100%; margin-top: 20px;">
    <tr>
        <td style="width: 50%; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd;">
            <strong>Responsável:</strong><br>
            Nome: {{ $data['user']['name'] }}<br>
            Email: {{ $data['user']['email'] }}<br>
            Função: {{ $data['user']['sector'] }}<br>
            Telefone: {{ $data['user']['phone'] ?? 'Não informado' }}
        </td>
        <td style="width: 50%; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd;">
            <strong>Informações do Pedido:</strong><br>
            Pedido ID: {{ $data['purchaseData']['id'] }}<br>
            Fornecedor: {{ $data['purchaseData']['supplier']['corporate_name'] }}<br>
            CNPJ: {{ $data['purchaseData']['supplier']['cnpj'] }}<br>
            Gerado em: {{ $data['generateAt'] }}<br>
            Expira em: {{ $data['expiresAt'] }}
        </td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <thead>
    <tr style="background-color: #007BFF; color: #fff;">
        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">#</th>
        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Produto</th>
        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Descrição</th>
        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Quantidade</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data['purchaseData']['purchaseItems'] as $item)
        <tr>
            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item['id'] }}</td>
            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item['product']['name'] }}</td>
            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item['product']['description'] ?? 'Não disponível' }}</td>
            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item['product_quantity'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<footer style="margin-top: 30px; text-align: center; font-size: 14px; color: #555;">
    <p>Documento gerado automaticamente pelo Sistema RCE</p>
    <p>&copy; {{ date('Y') }} RCE - Todos os direitos reservados</p>
</footer>
</body>
</html>
