import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface DayHours {
  weekday: number;
  open: boolean;
  open_time: string | null;
  close_time: string | null;
  breaks: { from: string; to: string }[];
}

export interface SpecialClosing {
  id: string;
  date_from: string;
  date_to: string;
  reason: string | null;
  full_day: boolean;
  open_time: string | null;
  close_time: string | null;
}

export interface CreateSpecialClosingDto {
  date_from: string;
  date_to: string;
  reason?: string;
  full_day: boolean;
  open_time?: string;
  close_time?: string;
}

@Injectable({ providedIn: 'root' })
export class OpeningHoursService {
  private readonly http = inject(HttpClient);
  private readonly base = '/api/v1/admin';

  getHours(): Observable<DayHours[]> {
    return this.http.get<DayHours[]>(`${this.base}/opening-hours`);
  }

  saveHours(days: DayHours[]): Observable<{ ok: boolean }> {
    return this.http.put<{ ok: boolean }>(`${this.base}/opening-hours`, days);
  }

  getSpecialClosings(): Observable<SpecialClosing[]> {
    return this.http.get<SpecialClosing[]>(`${this.base}/special-closings`);
  }

  createSpecialClosing(dto: CreateSpecialClosingDto): Observable<SpecialClosing> {
    return this.http.post<SpecialClosing>(`${this.base}/special-closings`, dto);
  }

  deleteSpecialClosing(closingId: string): Observable<{ ok: boolean }> {
    return this.http.delete<{ ok: boolean }>(`${this.base}/special-closings/${closingId}`);
  }
}
