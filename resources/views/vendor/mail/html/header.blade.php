<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1c/Kab.Kerinci.svg/100px-Kab.Kerinci.svg.png" class="logo" alt="Dikjar Kerinci">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
