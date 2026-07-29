<div class="ticket-card {{ $ticket->status }} ticket-list-item {{ $ticket->is_pinned ? 'is-pinned' : '' }} {{ $ticket->is_favorite ? 'is-favorite' : '' }}"
    data-ticket-id="{{ $ticket->id }}" style="position: relative; padding-top: 24px;">

    {{-- Absolute Ticket Code & Date at Top Right --}}
    <span class="ticket-id"
        style="position: absolute; top: 8px; right: 20px; font-weight: 700; font-size: 11px; margin: 0; color: var(--slate); opacity: 0.7;"><span
            class="ticket-code-text">{{ $ticket->code }}</span> <span
            style="font-weight: 500; margin-left: 6px; color: var(--muted);">({{ $ticket->created_at->locale('id')->translatedFormat('d M Y - H:i') }})</span></span>

    {{-- Leftmost Side: Student Info, Pin and Favorite Buttons --}}
    <div class="ticket-student-col"
        style="flex: 0 0 250px; min-width: 0; display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%;">
        <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex-grow: 1;">
            {{-- Action buttons (Pin and Star) wrapped in a vertical stack --}}
            <div style="display: flex; flex-direction: column; align-items: center; gap: 8px; flex-shrink: 0;">
                {{-- Pin/Unpin Button --}}
                <form method="POST" action="{{ route('tickets.pinned', $ticket) }}"
                    style="display: inline; line-height: 1; margin: 0;">
                    @csrf
                    <button type="submit"
                        style="background: none; border: none; cursor: pointer; color: {{ $ticket->is_pinned ? '#f59e0b' : '#d1d5db' }}; font-size: 1.1rem; padding: 2px; line-height: 1;"
                        title="{{ $ticket->is_pinned ? 'Lepas Sematan' : 'Sematkan Tiket' }}">
                        <i class="fa-solid fa-thumbtack"
                            style="{{ $ticket->is_pinned ? '' : 'transform: rotate(-45deg);' }}"></i>
                    </button>
                </form>
                {{-- Favorite Star Button --}}
                <form method="POST" action="{{ route('tickets.favorite', $ticket) }}"
                    style="display: inline; line-height: 1; margin: 0;">
                    @csrf
                    <button type="submit"
                        style="background: none; border: none; cursor: pointer; color: {{ $ticket->is_favorite ? '#f59e0b' : '#d1d5db' }}; font-size: 1.1rem; padding: 2px; line-height: 1;"
                        title="{{ $ticket->is_favorite ? 'Batal Favorit' : 'Jadikan Favorit' }}">
                        <i class="fa-{{ $ticket->is_favorite ? 'solid' : 'regular' }} fa-star"></i>
                    </button>
                </form>
            </div>

            {{-- Avatar & Name/Class --}}
            @if($ticket->student || $ticket->student_name)
                @if(auth()->user()->isSiswa() && $ticket->anonymous)
                    <div class="ticket-guru-avatar" style="flex-shrink: 0; margin: 0; background: #e2e8f0; color: #475569;"><i
                            class="fas fa-user-secret"></i></div>
                    <div style="min-width: 0; display: flex; flex-direction: column; gap: 2px;">
                        <div
                            style="font-size: 14px; font-weight: 600; color: var(--navy); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.2;">
                            Anonim</div>

                        {{-- Desktop Status Badges (Shown on Desktop, Hidden on Mobile) --}}
                        <div class="desktop-status-badges"
                            style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;">
                            <span class="badge badge-{{ $ticket->status }} ticket-status-badge"
                                style="margin: 0; width: fit-content; padding: 2px 8px; font-size: 10px; border-radius: 4px; line-height: 1.2; font-weight: 700;">{{ $ticket->status_label }}</span>
                            @if($ticket->anonymous)
                                <span class="badge"
                                    style="background-color: #fef2f2; color: #ef4444; border: 1px solid #fecaca; margin: 0; width: fit-content; padding: 2px 8px; font-size: 9px; border-radius: 4px; line-height: 1.2; font-weight: 600;"
                                    title="Pengajuan sebagai Anonim">
                                    <i class="fas fa-user-secret"></i> Anonim
                                </span>
                            @endif
                        </div>
                    </div>
                @else
                    @php
                        $studentName = $ticket->student_name ?? $ticket->student?->user?->name ?? 'Siswa';
                        $words = explode(' ', $studentName);
                        $initials = count($words) >= 2
                            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                            : strtoupper(substr($studentName, 0, 2));
                    @endphp
                    <div class="ticket-guru-avatar" style="flex-shrink: 0; margin: 0;">{{ $initials }}</div>
                    <div style="min-width: 0; display: flex; flex-direction: column; gap: 2px;">
                        <div
                            style="font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; line-height: 1;">
                            {{ $ticket->class_name ?? $ticket->class->name ?? $ticket->student?->class?->name ?? '-' }}</div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--navy); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.2;"
                            title="{{ $studentName }}">{{ $studentName }}</div>

                        {{-- Desktop Status Badges (Shown on Desktop, Hidden on Mobile) --}}
                        <div class="desktop-status-badges"
                            style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;">
                            <span class="badge badge-{{ $ticket->status }} ticket-status-badge"
                                style="margin: 0; width: fit-content; padding: 2px 8px; font-size: 10px; border-radius: 4px; line-height: 1.2; font-weight: 700;">{{ $ticket->status_label }}</span>
                            @if($ticket->anonymous)
                                <span class="badge"
                                    style="background-color: #fef2f2; color: #ef4444; border: 1px solid #fecaca; margin: 0; width: fit-content; padding: 2px 8px; font-size: 9px; border-radius: 4px; line-height: 1.2; font-weight: 600;"
                                    title="Pengajuan sebagai Anonim">
                                    <i class="fas fa-user-secret"></i> Anonim
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <span class="text-muted" style="font-size:12px;">Siswa tidak ditemukan</span>
            @endif
        </div>

        {{-- Mobile Status Badges (Hidden on Desktop, Shown on Mobile) --}}
        @if($ticket->student || $ticket->student_name)
            <div class="mobile-status-badges"
                style="display: none; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0;">
                <span class="badge badge-{{ $ticket->status }} ticket-status-badge"
                    style="margin: 0; width: fit-content; padding: 3px 8px; font-size: 10px; border-radius: 4px; line-height: 1.2; font-weight: 700; white-space: nowrap;">{{ $ticket->status_label }}</span>
                @if($ticket->anonymous)
                    <span class="badge"
                        style="background-color: #fef2f2; color: #ef4444; border: 1px solid #fecaca; margin: 0; width: fit-content; padding: 2px 8px; font-size: 9px; border-radius: 4px; line-height: 1.2; font-weight: 600; white-space: nowrap;"
                        title="Pengajuan sebagai Anonim">
                        <i class="fas fa-user-secret"></i> Anonim
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Middle: Badges, Title & Desc --}}
    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px;">
        {{-- Badges row --}}
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            @if($ticket->service)
                <span class="badge"
                    style="background: {{ $ticket->service->color ?? '#3d5454' }}; color: #fff; display: inline-flex; align-items: center; gap: 4px; font-weight: 600; font-size: 10px; padding: 2px 8px; border-radius: 4px; margin: 0; width: fit-content;">
                    <i
                        class="fas {{ str_starts_with($ticket->service->icon ?? 'fa-tag', 'fas ') ? Str::after($ticket->service->icon, 'fas ') : ($ticket->service->icon ?? 'fa-tag') }}"></i>
                    {{ $ticket->service->name }}
                </span>
            @endif
        </div>

        {{-- Title & Description --}}
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <div class="ticket-title"
                style="margin: 0; font-size: 15px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 6px;">
                {{ $ticket->title }}
            </div>
            <div class="ticket-desc"
                style="margin: 0; font-size: 13px; color: var(--slate); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $ticket->description }}</div>
            @if($ticket->status === 'dibatalkan' && $ticket->cancel_reason)
                <div
                    style="margin-top: 6px; font-size: 12px; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; padding: 6px 10px; border-radius: 6px; text-align: left; width: fit-content; max-width: 100%; white-space: normal; word-break: break-word;">
                    <i class="fas fa-info-circle"></i> <strong>Dibatalkan:</strong> {{ $ticket->cancel_reason }}
                </div>
            @endif
        </div>
    </div>

    <div
        style="display: flex; gap: 8px; justify-content: flex-end; align-items: center; flex-shrink: 0; position: relative;">
        <button type="button" class="btn btn-secondary btn-sm btn-detail-ticket" title="Detail Masalah"
            onclick="openDetailModal(this)" data-id="{{ $ticket->id }}" data-status="{{ $ticket->status }}"
            data-code="{{ $ticket->code }}" data-title="{{ $ticket->title }}"
            data-description="{{ $ticket->description }}"
            data-student="{{ $ticket->student_name ?? $ticket->student?->user?->name ?? 'Anonim' }}"
            data-class="{{ $ticket->class?->name ?? $ticket->student?->class?->name ?? '-' }}"
            data-teacher="{{ $ticket->teacher?->user?->name ?? 'Belum Ditentukan' }}"
            data-service="{{ $ticket->service?->name ?? '-' }}"
            data-service-color="{{ $ticket->service?->color ?? '#3d5454' }}"
            data-service-icon="fas {{ str_starts_with($ticket->service?->icon ?? 'fa-tag', 'fas ') ? Str::after($ticket->service->icon, 'fas ') : ($ticket->service?->icon ?? 'fa-tag') }}"
            data-prior-action="{{ $ticket->prior_action ?? '' }}" data-anonymous="{{ $ticket->anonymous ? '1' : '0' }}"
            data-cancel-reason="{{ $ticket->cancel_reason ?? '' }}"
            data-date="{{ $ticket->created_at->translatedFormat('d M Y') }}"
            style="padding: 6px 12px; font-size: 12px; margin: 0; display: inline-flex; align-items: center; gap: 4px;">
            <i class="fas fa-info-circle"></i> Detail
        </button>
        @if($ticket->status !== 'selesai' && $ticket->status !== 'dibatalkan')
            <a href="{{ route('chat.show', $ticket) }}" class="btn btn-primary btn-sm btn-balas"
                data-ticket-id="{{ $ticket->id }}"
                style="padding: 6px 12px; font-size: 12px; margin: 0; display: inline-flex; align-items: center; gap: 4px; position: relative;">
                <i class="fas fa-comments"></i> Balas
                @if($ticket->unread_count > 0)
                    <span class="unread-badge"
                        style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid white; line-height: 1;">
                        {{ $ticket->unread_count > 9 ? '9+' : $ticket->unread_count }}
                    </span>
                @endif
            </a>
        @else
            <a href="{{ route('chat.show', $ticket) }}" class="btn btn-secondary btn-sm"
                style="padding: 6px 12px; font-size: 12px; margin: 0; display: inline-flex; align-items: center; gap: 4px;"><i
                    class="fas fa-eye"></i> Lihat</a>
        @endif

        {{-- Dropdown Actions for Guru BK --}}
        @if(auth()->user()->isGuru() && $ticket->status !== 'selesai' && $ticket->status !== 'dibatalkan')
            <div class="ticket-actions-dropdown" style="position: relative; display: inline-block;">
                <button type="button" class="btn btn-secondary btn-sm dropdown-trigger"
                    style="padding: 6px 10px; font-size: 12px; margin: 0; display: inline-flex; align-items: center; justify-content: center; height: 28px; background: transparent; border: 1px solid var(--slate-light, #cbd5e1); color: var(--slate);"
                    onclick="toggleActionsDropdown(event, '{{ $ticket->id }}')">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu-content" id="actions-dropdown-{{ $ticket->id }}"
                    style="display: none; position: absolute; right: 0; top: calc(100% + 5px); background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); z-index: 100; min-width: 170px; overflow: hidden; text-align: left;">
                    <a href="#"
                        style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; color: var(--teal); text-decoration: none; transition: background 0.2s; white-space: nowrap;"
                        onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='transparent'"
                        onclick="event.preventDefault(); closeAllDropdowns(); openSelesaiModal('{{ $ticket->id }}', '{{ addslashes($ticket->title) }}', '{{ addslashes($ticket->description) }}')">
                        <i class="fas fa-check-circle" style="width: 14px;"></i> Selesaikan Konsultasi
                    </a>
                    <a href="#"
                        style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; color: #ef4444; text-decoration: none; transition: background 0.2s; border-top: 1px solid #f1f5f9; white-space: nowrap;"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'"
                        onclick="event.preventDefault(); closeAllDropdowns(); openConfirmCancelModal('{{ $ticket->id }}')">
                        <i class="fas fa-ban" style="width: 14px;"></i> Batalkan Konsultasi
                    </a>
                </div>
            </div>
        @endif

        {{-- Delete Button for Guru BK on Cancelled Tickets --}}
        @if(auth()->user()->isGuru() && $ticket->status === 'dibatalkan')
            <button type="button"
                onclick="openDeleteTicketModal('{{ $ticket->id }}', '{{ addslashes($ticket->title) }}')"
                class="btn btn-sm"
                style="padding: 6px 10px; font-size: 12px; margin: 0; background: transparent; border: 1px solid #fca5a5; color: #ef4444; display: inline-flex; align-items: center; gap: 4px; border-radius: 6px; cursor: pointer; transition: background 0.2s;"
                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'"
                title="Hapus Tiket">
                <i class="fas fa-trash-alt"></i>
            </button>
        @endif
    </div>

</div>