import { Component, OnInit, inject, signal } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { FormsModule } from '@angular/forms';
import { ReservationsService } from '../../../services/reservations.service';
import { Reservation, ReservationStatus, STATUS_LABELS } from '../../../services/reservation.types';

@Component({
  selector: 'app-reservierungsliste',
  standalone: true,
  imports: [MatIconModule, FormsModule],
  templateUrl: './reservierungsliste.component.html',
  styleUrl: './reservierungsliste.component.scss',
})
export class ReservierungslisteComponent implements OnInit {

  private readonly svc = inject(ReservationsService);

  readonly reservations = this.svc.reservations;
  readonly total        = this.svc.total;
  readonly loading      = this.svc.loading;

  filterDate = '';
  filterStatus = '';
  actionError  = signal<string | null>(null);

  // ✅ PRINT STATE
  showPrintModal = signal(false);

  printMode: 'day' | 'range' = 'day';
  printFrom = '';
  printTo = '';
  printReservations: Reservation[] = [];

  selectedStatuses: string[] = [];

  readonly statusOptions = [
    { value: '',          label: 'Alle Status' },
    { value: 'pending',   label: 'Ausstehend' },
    { value: 'confirmed', label: 'Bestätigt' },
    { value: 'cancelled', label: 'Storniert' },
    { value: 'no_show',   label: 'No-Show' },
  ];

  // ─────────────────────────────────────────────────────────

  ngOnInit(): void {
    this.filterDate = this.getToday();
    this.load();
  }

  // ✅ FIX: korrektes lokales Datum (kein UTC shift)
  private getToday(): string {
    const today = new Date();
    const offset = today.getTimezoneOffset();
    const local = new Date(today.getTime() - offset * 60000);
    return local.toISOString().split('T')[0];
  }

  // ─────────────────────────────────────────────────────────
  // PRINT MODAL

  openPrintModal(): void {
    this.showPrintModal.set(true);

    // default setzen
    this.printMode = 'day';
    this.printFrom = this.filterDate;
    this.printTo   = this.filterDate;
    this.selectedStatuses = [];
  }

  closePrintModal(): void {
    this.showPrintModal.set(false);
  }

  toggleStatus(status: string): void {
    if (!status) return;

    if (this.selectedStatuses.includes(status)) {
      this.selectedStatuses = this.selectedStatuses.filter(s => s !== status);
    } else {
      this.selectedStatuses.push(status);
    }
  }

  // ─────────────────────────────────────────────────────────
  // PRINT LOGIK (FIXED)

  print(): void {
    let data = [...this.reservations()];

    // ✅ 1. Datum / Zeitraum korrekt filtern
    if (this.printMode === 'day') {
      data = data.filter(r =>
        r.starts_at.startsWith(this.printFrom)
      );
    } else if (this.printFrom && this.printTo) {
      data = data.filter(r => {
        const date = r.starts_at.split('T')[0];
        return date >= this.printFrom && date <= this.printTo;
      });
    }

    // ✅ 2. Status filtern
    if (this.selectedStatuses.length > 0) {
      data = data.filter(r =>
        this.selectedStatuses.includes(r.status)
      );
    }

    // ✅ 3. Sortieren (wichtig für Druck)
    data.sort((a, b) =>
      new Date(a.starts_at).getTime() - new Date(b.starts_at).getTime()
    );

    // speichern für Template
    this.printReservations = data;

    // Modal schließen
    this.closePrintModal();

    // kurz warten → dann drucken
    setTimeout(() => {
      window.print();

      // ✅ Reset danach (wichtig!)
      this.printReservations = [];

    }, 100);
  }

  // ─────────────────────────────────────────────────────────
  // DATA LOAD

  load(): void {
    this.actionError.set(null);

    this.svc.load({
      date:   this.filterDate   || undefined,
      status: this.filterStatus || undefined,
    }).subscribe();
  }

  resetToToday(): void {
    this.filterDate = this.getToday();
    this.load();
  }

  // ─────────────────────────────────────────────────────────
  // ACTIONS

  confirm(id: string): void {
    this.actionError.set(null);

    this.svc.confirm(id).subscribe({
      error: () => this.actionError.set('Bestätigung fehlgeschlagen.'),
    });
  }

  markNoShow(id: string): void {
    this.actionError.set(null);

    this.svc.markNoShow(id).subscribe({
      error: () => this.actionError.set('No-Show konnte nicht gesetzt werden.'),
    });
  }

  cancel(id: string): void {
    this.actionError.set(null);

    this.svc.cancel(id).subscribe({
      error: () => this.actionError.set('Stornierung fehlgeschlagen.'),
    });
  }

  // ─────────────────────────────────────────────────────────
  // HELPERS

  statusLabel(status: string): string {
    return STATUS_LABELS[status as ReservationStatus] ?? status;
  }

  formatTime(iso: string): string {
    return new Date(iso).toLocaleTimeString('de-DE', {
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  canConfirm(r: Reservation): boolean {
    return r.status === 'pending';
  }

  canMarkNoShow(r: Reservation): boolean {
    return r.status === 'pending' || r.status === 'confirmed';
  }

  canCancel(r: Reservation): boolean {
    return r.status === 'pending' || r.status === 'confirmed';
  }
}