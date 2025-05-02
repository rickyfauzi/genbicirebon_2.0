<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <table >
        <thead >
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Prodi</th>
                <th>Jenis Kelamin</th>
                <th>Keterangan</th>
                <th>Waktu Submit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pembinaAbsen as $key=>$list )
            <tr>
                <td>{{ ++$key }}</td>
                <td>{{ $list->nama }}</td>
                <td>{{ $list->prodi }}</td>
                <td>{{ $list->jk }}</td>                                                
                <td>
                    {{ $list->keterangan }}
                </td>
                <td>{{ $list->updated_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>   
</body>
</html>