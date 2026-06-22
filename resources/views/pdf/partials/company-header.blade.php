<table class="naj-header">
    <tr>
        <td class="naj-logo">NAJ</td>
        <td>
            <div class="naj-company">PT. NUSANTARA ABADI JAYA</div>
            <div>JL. Wiyata No. 81 RT 23, Kalimantan Timur</div>
            @if ($subtitle ?? null)
                <div>{{ $subtitle }}</div>
            @endif
        </td>
        @if ($title ?? null)
            <td class="naj-title-cell"><div class="naj-title">{{ $title }}</div></td>
        @endif
    </tr>
</table>
