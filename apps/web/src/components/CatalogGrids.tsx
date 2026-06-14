import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useRefetchWhenTabVisible } from '../hooks/useRefetchWhenTabVisible';
import { useSupabaseTablesReload } from '../hooks/useSupabaseTablesReload';
import { adminPanelUrl } from '../lib/adminUrl';
import { excerpt, formatMoney, initials, isHttpUrl } from '../lib/catalogUtils';
import { fetchEspecialidades, fetchMedicos, fetchServicios } from '../lib/remoteCatalog';
import type { EspecialidadRow, MedicoRow, ServicioRow } from '../types/catalogRows';

export type { EspecialidadRow, MedicoRow, ServicioRow } from '../types/catalogRows';

const SB_RT_ESP: readonly string[] = ['especialidades'];
const SB_RT_MED: readonly string[] = ['medicos'];
const SB_RT_SRV: readonly string[] = ['servicios'];
const SB_RT_ALL: readonly string[] = ['especialidades', 'medicos', 'servicios'];

export function LiveStats() {
  const [esp, setEsp] = useState<number | string>('—');
  const [med, setMed] = useState<number | string>('—');
  const [srv, setSrv] = useState<number | string>('—');

  const load = useCallback(async (opts?: { silent?: boolean }) => {
    const silent = Boolean(opts?.silent);
    try {
      const [e, m, s] = await Promise.all([fetchEspecialidades(), fetchMedicos(), fetchServicios()]);
      setEsp(Array.isArray(e) ? e.length : '—');
      setMed(Array.isArray(m) ? m.length : '—');
      setSrv(Array.isArray(s) ? s.length : '—');
    } catch {
      if (!silent) {
        setEsp('—');
        setMed('—');
        setSrv('—');
      }
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  useRefetchWhenTabVisible(() => void load({ silent: true }));
  useSupabaseTablesReload(SB_RT_ALL, () => void load({ silent: true }));

  return (
    <dl className="hero__stats" aria-label="Indicadores (datos en vivo desde el sistema)">
      <div className="stat">
        <dt className="stat__kpi">{esp}</dt>
        <dd className="stat__label">Especialidades</dd>
      </div>
      <div className="stat">
        <dt className="stat__kpi">{med}</dt>
        <dd className="stat__label">Médicos</dd>
      </div>
      <div className="stat">
        <dt className="stat__kpi">{srv}</dt>
        <dd className="stat__label">Servicios</dd>
      </div>
    </dl>
  );
}

export function CatalogEspecialidades() {
  const [state, setState] = useState<'loading' | 'ok' | 'empty' | 'err'>('loading');
  const [list, setList] = useState<EspecialidadRow[]>([]);
  const [err, setErr] = useState('');

  const load = useCallback(async (opts?: { silent?: boolean }) => {
    const silent = Boolean(opts?.silent);
    if (!silent) {
      setState('loading');
      setErr('');
    }
    try {
      const data = await fetchEspecialidades();
      if (!Array.isArray(data) || data.length === 0) {
        setState('empty');
        return;
      }
      setList(data);
      setState('ok');
    } catch (e) {
      if (silent) {
        return;
      }
      setErr(e instanceof Error ? e.message : 'Error');
      setState('err');
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  useRefetchWhenTabVisible(() => void load({ silent: true }));
  useSupabaseTablesReload(SB_RT_ESP, () => void load({ silent: true }));

  if (state === 'loading') {
    return (
      <div className="catalog-loading">
        <div className="catalog-skeleton" />
        <p className="muted">Cargando especialidades…</p>
      </div>
    );
  }

  if (state === 'empty') {
    return (
      <div className="catalog-empty card card--soft">
        <p className="muted" style={{ margin: 0 }}>
          No hay especialidades en la base de datos. Créalas en el{' '}
          <a className="card__link" href={adminPanelUrl()}>
            panel de administración
          </a>
          .
        </p>
      </div>
    );
  }

  if (state === 'err') {
    return (
      <div className="catalog-error card">
        <p style={{ margin: '0 0 8px', fontWeight: 700 }}>No se pudieron cargar las especialidades</p>
        <p className="muted" style={{ margin: '0 0 12px' }}>
          {err}
        </p>
        <button type="button" className="btn btn--primary catalog-retry" onClick={() => void load()}>
          Reintentar
        </button>
      </div>
    );
  }

  return (
    <div className="grid grid--3">
      {list.map((e) => {
        const img = isHttpUrl(e.imagen) ? (
          <div className="catalog-card__media">
            <img src={e.imagen!} alt="" loading="lazy" />
          </div>
        ) : (
          <div className="catalog-card__media catalog-card__media--placeholder" aria-hidden="true">
            🏥
          </div>
        );
        return (
          <article key={e.id} className="card card--catalog">
            {img}
            <h3 className="card__title">{e.nombre}</h3>
            <p className="card__text">Consultas y procedimientos bajo esta especialidad.</p>
            <Link className="card__link" to="/cita">
              Consultar
            </Link>
          </article>
        );
      })}
    </div>
  );
}

export function CatalogMedicos() {
  const [state, setState] = useState<'loading' | 'ok' | 'empty' | 'err'>('loading');
  const [list, setList] = useState<MedicoRow[]>([]);
  const [err, setErr] = useState('');

  const load = useCallback(async (opts?: { silent?: boolean }) => {
    const silent = Boolean(opts?.silent);
    if (!silent) {
      setState('loading');
      setErr('');
    }
    try {
      const data = await fetchMedicos();
      if (!Array.isArray(data) || data.length === 0) {
        setState('empty');
        return;
      }
      setList(data);
      setState('ok');
    } catch (e) {
      if (silent) {
        return;
      }
      setErr(e instanceof Error ? e.message : 'Error');
      setState('err');
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  useRefetchWhenTabVisible(() => void load({ silent: true }));
  useSupabaseTablesReload(SB_RT_MED, () => void load({ silent: true }));

  if (state === 'loading') {
    return (
      <div className="catalog-loading">
        <div className="catalog-skeleton" />
        <p className="muted">Cargando equipo médico…</p>
      </div>
    );
  }

  if (state === 'empty') {
    return (
      <div className="catalog-empty card card--soft">
        <p className="muted" style={{ margin: 0 }}>
          No hay médicos registrados. Añádelos en el{' '}
          <a className="card__link" href={adminPanelUrl()}>
            panel de administración
          </a>
          .
        </p>
      </div>
    );
  }

  if (state === 'err') {
    return (
      <div className="catalog-error card">
        <p style={{ margin: '0 0 8px', fontWeight: 700 }}>No se pudo cargar el equipo médico</p>
        <p className="muted" style={{ margin: '0 0 12px' }}>
          {err}
        </p>
        <button type="button" className="btn btn--primary catalog-retry" onClick={() => void load()}>
          Reintentar
        </button>
      </div>
    );
  }

  return (
    <div className="grid grid--4">
      {list.map((m) => {
        const role = m.especialidad?.nombre ?? '—';
        const av = isHttpUrl(m.foto) ? (
          <div className="avatar avatar--photo">
            <img src={m.foto!} alt="" loading="lazy" />
          </div>
        ) : (
          <div className="avatar" aria-hidden="true">
            {initials(m.nombre)}
          </div>
        );
        return (
          <article key={m.id} className="profile profile--catalog">
            {av}
            <h3 className="profile__name">{m.nombre}</h3>
            <p className="profile__role">{role}</p>
          </article>
        );
      })}
    </div>
  );
}

export function CatalogServicios() {
  const [state, setState] = useState<'loading' | 'ok' | 'empty' | 'err'>('loading');
  const [list, setList] = useState<ServicioRow[]>([]);
  const [err, setErr] = useState('');

  const load = useCallback(async (opts?: { silent?: boolean }) => {
    const silent = Boolean(opts?.silent);
    if (!silent) {
      setState('loading');
      setErr('');
    }
    try {
      const data = await fetchServicios();
      if (!Array.isArray(data) || data.length === 0) {
        setState('empty');
        return;
      }
      setList(data);
      setState('ok');
    } catch (e) {
      if (silent) {
        return;
      }
      setErr(e instanceof Error ? e.message : 'Error');
      setState('err');
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  useRefetchWhenTabVisible(() => void load({ silent: true }));
  useSupabaseTablesReload(SB_RT_SRV, () => void load({ silent: true }));

  if (state === 'loading') {
    return (
      <div className="catalog-loading">
        <div className="catalog-skeleton" />
        <p className="muted">Cargando servicios…</p>
      </div>
    );
  }

  if (state === 'empty') {
    return (
      <div className="catalog-empty card card--soft">
        <p className="muted" style={{ margin: 0 }}>
          No hay servicios publicados. Créalos en el{' '}
          <a className="card__link" href={adminPanelUrl()}>
            panel de administración
          </a>
          .
        </p>
      </div>
    );
  }

  if (state === 'err') {
    return (
      <div className="catalog-error card">
        <p style={{ margin: '0 0 8px', fontWeight: 700 }}>No se pudieron cargar los servicios</p>
        <p className="muted" style={{ margin: '0 0 12px' }}>
          {err}
        </p>
        <button type="button" className="btn btn--primary catalog-retry" onClick={() => void load()}>
          Reintentar
        </button>
      </div>
    );
  }

  return (
    <div className="grid grid--3">
      {list.map((s) => {
        const med = s.medico?.nombre ?? '—';
        const desc = excerpt(s.descripcion, 140);
        const price = formatMoney(s.precio);
        return (
          <article key={s.id} className="card card--catalog card--servicio">
            <p className="catalog-price">{price}</p>
            <h3 className="card__title">{s.nombre}</h3>
            <p className="card__text">{desc}</p>
            <p className="catalog-meta muted">Dr(a). {med}</p>
            <div className="row" style={{ gap: 10, flexWrap: 'wrap' }}>
              <Link className="card__link" to={`/pagar/${s.id}`}>
                Pagar en línea
              </Link>
              <Link className="card__link" to="/cita" style={{ opacity: 0.85 }}>
                Solicitar cita
              </Link>
            </div>
          </article>
        );
      })}
    </div>
  );
}
