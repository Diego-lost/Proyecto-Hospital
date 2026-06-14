import { Link } from 'react-router-dom';
import PageCover from '../../components/PageCover';
import { navGroups } from '../../config/hospitalNav';
import type { SeccionPortal } from './SeccionPortalPage';

const seccionLabel: Record<SeccionPortal, string> = {
  institucional: 'Institucional',
  organizacion: 'Organización',
  prensa: 'Prensa',
  atencion: 'Atención al ciudadano',
};

type Props = { seccion: SeccionPortal };

export default function PortalHubPage({ seccion }: Props) {
  const group = navGroups.find((g) => g.id === seccion);
  if (!group) {
    return (
      <main id="contenido" className="page-main">
        <PageCover title="Sección no disponible" subtitle="No hay contenido para esta ruta. Vuelva al inicio." />
        <section className="section section--after-cover">
          <div className="container">
            <Link className="btn btn--soft" to="/">
              Inicio
            </Link>
          </div>
        </section>
      </main>
    );
  }

  return (
    <main id="contenido" className="page-main">
      <PageCover
        pill={seccionLabel[seccion]}
        title={group.label}
        subtitle={`${group.groupDescription} Elija una sección para ver el contenido completo.`}
      />
      <section className="section section--after-cover" style={{ paddingBottom: '48px' }}>
        <div className="container" style={{ maxWidth: '900px' }}>
          <div className="grid grid--2" style={{ gap: '14px' }}>
            {group.children.map((item) => (
              <article key={item.label} className="card card--soft" style={{ margin: 0 }}>
                {item.href ? (
                  <a className="card__link" href={item.href} style={{ fontWeight: 700, fontSize: '1.05rem' }}>
                    {item.label}
                    <span aria-hidden="true"> ↗</span>
                  </a>
                ) : (
                  <Link className="card__link" to={item.to ?? '/'} style={{ fontWeight: 700, fontSize: '1.05rem' }}>
                    {item.label}
                  </Link>
                )}
                <p className="muted" style={{ margin: '10px 0 0', lineHeight: 1.55 }}>
                  {item.description}
                </p>
              </article>
            ))}
          </div>

          <p style={{ marginTop: '28px' }}>
            <Link className="btn btn--soft" to="/">
              Volver al inicio
            </Link>
          </p>
        </div>
      </section>
    </main>
  );
}
