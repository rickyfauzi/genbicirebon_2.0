<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        h1 {
            color: #333; /* Title color */
            padding: 20px; /* Padding around the title */
            border-radius: 5px; /* Rounded corners */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .no-data {
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>
    <h1>REKAP KEGIATAN</h1>
<table>
    <thead>
        <tr>
                <th>No</th>
                <th>Nama Kegiatan</th>
                <th>Tanggal Pelaksanaan</th>
                <th>Komisariat</th>
                <th>Jenis Kegiatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key=>$list)
        <tr>
            <td class="text-center">{{ ++$key }}</td>          
            <td>{{ $list->nama_kegiatan }}</td>
            <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
            <td>
            @if($list->id_komisariat == 2)
                    Komisariat GenBI Universitas Majalengka
                @elseif($list->id_komisariat == 3)
                    Komisariat GenBI Universitas Kuningan
                @elseif($list->id_komisariat == 4)
                    Komisariat GenBI Universitas Wiralodra
                @elseif($list->id_komisariat == 5)
                    Komisariat GenBI Universitas Islam Bunga Bangsa Cirebon
                @elseif($list->id_komisariat == 6)
                    Komisariat GenBI IAIN Syekh Nurjati Cirebon
                @elseif($list->id_komisariat == 7)
                    Komisariat GenBI Universitas Swadaya Gunung Jati Cirebon
                @else
                    No Komisariat
            @endif   
            </td>                             
            <td>{{ $list->jenis }}</td>                                     
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>