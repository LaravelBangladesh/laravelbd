@props(['label'])
<table class="detail" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="detail-label">{{ $label }}</td>
</tr>
<tr>
<td class="detail-value">{{ $slot }}</td>
</tr>
</table>
