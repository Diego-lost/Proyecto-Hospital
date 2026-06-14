import { Link, useParams } from 'react-router-dom';
import PageCover from '../../components/PageCover';
import {
  atencionPages,
  institucionalPages,
  organizacionPages,
  prensaPages,
  type PortalDoc,
} from '../../config/portalContent';

export type SeccionPortal = 'institucional' | 'organizacion' | 'prensa' | 'atencion';

const tables: Record<SeccionPortal, Record<string, PortalDoc>> = {
  institucional: institucionalPages,
  organizacion: organizacionPages,
  prensa: prensaPages,
  atencion: atencionPages,
};

const seccionLabel: Record<SeccionPortal, string> = {
  institucional: 'Institucional',
  organizacion: 'Organización',
  prensa: 'Prensa',
  atencion: 'Atención al ciudadano',
};

type Props = { seccion: SeccionPortal };

export default function SeccionPortalPage({ seccion }: Props) {
  const { slug } = useParams<{ slug: string }>();
  const doc = slug ? tables[seccion]?.[slug] : undefined;

  if (!slug || !doc) {
    return (
      <main id="contenido" className="page-main">
        <PageCover title="Página no encontrada" subtitle="La dirección no coincide con un documento publicado." />
        <section className="section section--after-cover">
          <div className="container">
            <p className="muted">
              <Link className="card__link" to="/">
                Volver al inicio
              </Link>
            </p>
          </div>
        </section>
      </main>
    );
  }

  return (
    <main id="contenido" className="page-main">
      <PageCover pill={seccionLabel[seccion]} title={doc.title} subtitle={doc.intro} />
      <section className="section section--after-cover" style={{ paddingBottom: '48px' }}>
        <div className="container" style={{ maxWidth: '820px' }}>
          {doc.body?.map((p, i) => (
            <p key={i} style={{ marginBottom: '16px', lineHeight: 1.65 }}>
              {p}
            </p>
          ))}

          {doc.bullets && doc.bullets.length > 0 ? (
            <ul style={{ margin: '0 0 20px 0', paddingLeft: '1.25rem', lineHeight: 1.65 }}>
              {doc.bullets.map((item, i) => (
                <li key={i} style={{ marginBottom: '8px' }}>
                  {item}
                </li>
              ))}
            </ul>
          ) : null}

          {doc.links && doc.links.length > 0 ? (
            <div className="card card--soft" style={{ marginTop: '8px', marginBottom: '20px' }}>
              <p className="muted" style={{ margin: '0 0 12px 0', fontWeight: 600 }}>
                Enlaces útiles
              </p>
              <ul style={{ margin: 0, paddingLeft: '1.25rem' }}>
                {doc.links.map((l) => (
                  <li key={l.href} style={{ marginBottom: '8px' }}>
                    <a className="card__link" href={l.href} target="_blank" rel="noopener noreferrer">
                      {l.label}
                    </a>
                  </li>
                ))}
              </ul>
            </div>
          ) : null}

          <div className="card card--soft">
            <p className="muted" style={{ margin: 0 }}>
              Textos editables desde administración o enlaces a PDF oficiales cuando la dirección lo apruebe. Estructura
              de referencia similar a portales de salud pública como{' '}
              <a className="card__link" href="https://www.hcllh.gob.pe/" target="_blank" rel="noopener noreferrer">
                hospitales de referencia en el Perú
              </a>
              .
            </p>
          </div>

          <p style={{ marginTop: '20px' }}>
            <Link className="btn btn--soft" to="/">
              Inicio
            </Link>
          </p>
        </div>
      </section>
    </main>
  );
}
