<table>
  <thead>
    <tr>
      <th width="5" align="center" style="background-color: #C4D79B; border-bottom: #687252; font-weight: bold;">No.</th>
      <th width="16" align="center" style="background-color: #C4D79B; border-bottom: #687252; font-weight: bold;">Nomor VA</th>
      <th width="30" align="center" style="background-color: #C4D79B; border-bottom: #687252; font-weight: bold;">Nama Siswa</th>
      <th width="15" align="center" style="background-color: #C4D79B; border-bottom: #687252; font-weight: bold;">Jumlah Tagihan</th>
      <th width="18" align="center" style="background-color: #C4D79B; border-bottom: #687252; font-weight: bold;">Rincian</th>
    </tr>
  </thead>
  <tbody>
  @foreach ($vas as $num => $va)
    @php
      $description = json_decode($va->description)
    @endphp
    <tr>
      <td data-format="0">{{ $num + 1 }}</td>
      <td data-format="0">{{ $va->number }}</td>
      <td>{{ $va->user->name }}</td>
      <td data-format="#,##0_);(#,##0)">{{ $va->outstanding }}</td>
      <td>
        @if ($description)
          @foreach ($description as $index => $desc)
            {{ $desc->name }}: {{ $desc->value }}

            @if ($index < sizeof($description) - 1)
            <br/>
            @endif
          @endforeach
        @else
        <span>-</span>
        @endif
      </td>
    </tr>
  @endforeach
  </tbody>
</table>