@props(['label'])
<table class="code" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="code-label">{{ $label }}</td>
</tr>
<tr>
<td class="code-value">{{ $slot }}</td>
</tr>
</table>
