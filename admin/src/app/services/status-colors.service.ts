import { Injectable, computed, signal } from '@angular/core';
import { FloorReservationStatus } from './floor-reservation.types';

/**
 * Operative Status-Schlüssel — feiner als der reine Lifecycle (FloorReservationStatus).
 * Wird in der „Status, Hinweise & Gäste typen"-Einstellungsseite konfiguriert.
 */
export type StatusKey =
  | 'waiting'    // Geplant, noch nicht eingecheckt
  | 'active'     // Eingecheckt / Tisch belegt
  | 'late'       // Verspätet (UI-Reserve, Lifecycle hat das (noch) nicht)
  | 'paid'       // Bezahlt (UI-Reserve)
  | 'completed'  // Beendet
  | 'no_show'    // Nicht erschienen
  | 'cancelled'; // Storniert

export interface StatusColor {
  key: StatusKey;
  label: string;
  color: string;
  icon?: string;
}

const STORAGE_KEY = 'lyandro.status-colors.v1';

const DEFAULT_STATUSES: StatusColor[] = [
  { key: 'waiting',   label: 'Warteliste', color: '#f59e0b', icon: 'circle' },
  { key: 'active',    label: 'Aktiv',      color: '#22c55e', icon: 'check' },
  { key: 'late',      label: 'Verspätet',  color: '#eab308', icon: 'clock' },
  { key: 'paid',      label: 'Bezahlt',    color: '#3b82f6', icon: 'money' },
  { key: 'completed', label: 'Beendet',    color: '#6b7280', icon: 'done' },
  { key: 'no_show',   label: 'No Show',    color: '#991b1b', icon: 'close' },
  { key: 'cancelled', label: 'Storniert',  color: '#9ca3af', icon: 'minus' },
];

/**
 * Lifecycle-Status (FloorReservationStatus) → operativer Status (StatusKey).
 * Wird benötigt, weil eine Reservierung im Backend einen reduzierten Lifecycle hat,
 * die UI-Farbpalette aber feiner ist.
 */
const LIFECYCLE_TO_OPERATIONAL: Record<FloorReservationStatus, StatusKey> = {
  upcoming:   'waiting',
  seated:     'active',
  completed:  'completed',
  cancelled:  'cancelled',
  'no-show':  'no_show',
};

@Injectable({ providedIn: 'root' })
export class StatusColorsService {
  private readonly _statuses = signal<StatusColor[]>(this.load());

  readonly statuses = computed(() => this._statuses());

  /** Liefert die Farbe für einen operativen Status-Schlüssel. */
  colorFor(key: StatusKey): string {
    return this._statuses().find(s => s.key === key)?.color
        ?? DEFAULT_STATUSES.find(s => s.key === key)?.color
        ?? '#6b7280';
  }

  /** Liefert die Farbe für eine Reservierung anhand ihres Lifecycle-Status. */
  colorForReservation(status: FloorReservationStatus): string {
    return this.colorFor(LIFECYCLE_TO_OPERATIONAL[status]);
  }

  /** Liefert das Label für einen operativen Status-Schlüssel. */
  labelFor(key: StatusKey): string {
    return this._statuses().find(s => s.key === key)?.label
        ?? DEFAULT_STATUSES.find(s => s.key === key)?.label
        ?? key;
  }

  /** Komplette Liste der Status-Definitionen ersetzen + persistieren. */
  setAll(list: StatusColor[]): void {
    this._statuses.set(list.map(s => ({ ...s })));
    this.persist();
  }

  /** Eine einzelne Eigenschaft (Farbe / Label) aktualisieren + persistieren. */
  patch(key: StatusKey, patch: Partial<Pick<StatusColor, 'color' | 'label' | 'icon'>>): void {
    this._statuses.update(list => list.map(s => s.key === key ? { ...s, ...patch } : s));
    this.persist();
  }

  /** Auf Defaults zurücksetzen. */
  reset(): void {
    this._statuses.set(DEFAULT_STATUSES.map(s => ({ ...s })));
    this.persist();
  }

  /** Defaults öffentlich verfügbar machen (z. B. für die Einstellungsseite). */
  defaults(): StatusColor[] {
    return DEFAULT_STATUSES.map(s => ({ ...s }));
  }

  // ── Persistence ─────────────────────────────────────────────────────────
  private load(): StatusColor[] {
    if (typeof localStorage === 'undefined') return DEFAULT_STATUSES.map(s => ({ ...s }));
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return DEFAULT_STATUSES.map(s => ({ ...s }));
      const parsed = JSON.parse(raw) as StatusColor[];
      // Mit Defaults mergen, damit neue Status-Keys später funktionieren
      const merged = DEFAULT_STATUSES.map(def => {
        const found = parsed.find(p => p.key === def.key);
        return found ? { ...def, ...found } : def;
      });
      return merged;
    } catch {
      return DEFAULT_STATUSES.map(s => ({ ...s }));
    }
  }

  private persist(): void {
    if (typeof localStorage === 'undefined') return;
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(this._statuses()));
    } catch { /* ignore */ }
  }
}
