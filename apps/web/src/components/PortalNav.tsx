import { useEffect, useRef, useState } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { navGroups, navStandaloneHints, portalTransparenciaHref, type NavGroup } from '../config/hospitalNav';

function MegaGroup({ group, onNavigate }: { group: NavGroup; onNavigate?: () => void }) {
  const [open, setOpen] = useState(false);
  const rootRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!open) return;
    const onDoc = (e: MouseEvent) => {
      if (rootRef.current && !rootRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener('click', onDoc);
    return () => document.removeEventListener('click', onDoc);
  }, [open]);

  return (
    <div
      ref={rootRef}
      className={`nav-mega${open ? ' nav-mega--open' : ''}`}
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
    >
      <button
        type="button"
        className="nav-mega__trigger"
        aria-expanded={open}
        aria-haspopup="true"
        onClick={() => setOpen((v) => !v)}
      >
        {group.label}
        <span className="nav-mega__chev" aria-hidden="true">
          ▾
        </span>
      </button>
      <div className="nav-mega__panel" role="menu">
        <p className="nav-mega__intro">{group.groupDescription}</p>
        <Link className="nav-mega__hub" to={`/${group.id}`} role="menuitem" onClick={close}>
          Ver listado de secciones en una página
        </Link>
        {group.children.map((item) => {
          const close = () => {
            setOpen(false);
            onNavigate?.();
          };
          const inner = (
            <>
              <span className="nav-mega__link-title">{item.label}</span>
              {item.description ? <span className="nav-mega__link-desc">{item.description}</span> : null}
            </>
          );
          if (item.href) {
            return (
              <a key={item.label} className="nav-mega__link" href={item.href} role="menuitem" onClick={close}>
                {inner}
              </a>
            );
          }
          return (
            <Link key={item.label} className="nav-mega__link" to={item.to ?? '/'} role="menuitem" onClick={close}>
              {inner}
            </Link>
          );
        })}
      </div>
    </div>
  );
}

export function PortalNavDesktop({ onNavigate }: { onNavigate?: () => void }) {
  return (
    <nav className="nav-mega-row" aria-label="Navegación institucional">
      <NavLink className="menu__link" to="/" end title={navStandaloneHints.inicio}>
        Inicio
      </NavLink>
      {navGroups.map((g) => (
        <MegaGroup key={g.id} group={g} onNavigate={onNavigate} />
      ))}
      <a
        className="menu__link menu__link--external"
        href={portalTransparenciaHref}
        target="_blank"
        rel="noopener noreferrer"
        title={navStandaloneHints.transparencia}
      >
        Transparencia
      </a>
    </nav>
  );
}

export function PortalNavDrawer({ onNavigate }: { onNavigate: () => void }) {
  return (
    <div className="drawer-portal" role="navigation" aria-label="Menú institucional">
      <div className="drawer-portal__standalone">
        <NavLink className="drawer__link drawer__link--strong" to="/" onClick={onNavigate} title={navStandaloneHints.inicio}>
          Inicio
        </NavLink>
        <p className="drawer-portal__hint">{navStandaloneHints.inicio}</p>
      </div>
      {navGroups.map((g) => (
        <details key={g.id} className="drawer-portal__group">
          <summary className="drawer-portal__summary">{g.label}</summary>
          <p className="drawer-portal__group-hint">{g.groupDescription}</p>
          <div className="drawer-portal__subs">
            <Link className="drawer__link drawer-portal__hub-link" to={`/${g.id}`} onClick={onNavigate}>
              Ver listado completo de {g.label.toLowerCase()}
            </Link>
            {g.children.map((item) =>
              item.href ? (
                <div key={item.label} className="drawer-portal__item">
                  <a className="drawer__link" href={item.href} onClick={onNavigate}>
                    {item.label}
                  </a>
                  {item.description ? <p className="drawer-portal__hint">{item.description}</p> : null}
                </div>
              ) : (
                <div key={item.label} className="drawer-portal__item">
                  <Link className="drawer__link" to={item.to ?? '/'} onClick={onNavigate}>
                    {item.label}
                  </Link>
                  {item.description ? <p className="drawer-portal__hint">{item.description}</p> : null}
                </div>
              ),
            )}
          </div>
        </details>
      ))}
      <div className="drawer-portal__standalone">
        <a
          className="drawer__link drawer__link--strong"
          href={portalTransparenciaHref}
          target="_blank"
          rel="noopener noreferrer"
          onClick={onNavigate}
          title={navStandaloneHints.transparencia}
        >
          Portal Transparencia
        </a>
        <p className="drawer-portal__hint">{navStandaloneHints.transparencia}</p>
      </div>
    </div>
  );
}
