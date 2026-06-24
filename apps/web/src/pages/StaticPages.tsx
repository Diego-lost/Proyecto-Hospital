import { Link } from 'react-router-dom';
import PageCover from '../components/PageCover';
import { CatalogMedicos } from '../components/CatalogGrids';
import { CLINIC } from '../config/clinicInfo';

export function EquipoPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover
        title="Equipo médico"
        subtitle="Conoce a nuestros especialistas y el área en la que atienden."
      />
      <section className="section section--after-cover">
        <div className="container">
          <CatalogMedicos />
        </div>
      </section>
    </main>
  );
}

export function SedesPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover title="Sedes y horarios" subtitle="Atención de lunes a sábado. Emergencia 24/7." />
      <section className="section section--after-cover">
        <div className="container">
          <div className="grid grid--2">
            <article className="location">
              <h3 className="location__title">Sede Central</h3>
              <p className="location__text">{CLINIC.address}</p>
              <ul className="list">
                <li>
                  <strong>Consultas:</strong> Lun–Sáb 8:00–20:00
                </li>
                <li>
                  <strong>Laboratorio:</strong> Lun–Sáb 7:00–18:00
                </li>
                <li>
                  <strong>Emergencia:</strong> 24 horas
                </li>
              </ul>
            </article>
            <article className="location">
              <h3 className="location__title">Sede El Tambo</h3>
              <p className="location__text">Av. Progreso, El Tambo, Huancayo</p>
              <ul className="list">
                <li>
                  <strong>Consultas:</strong> Lun–Sáb 9:00–19:00
                </li>
                <li>
                  <strong>Imágenes:</strong> Lun–Sáb 9:00–17:00
                </li>
                <li>
                  <strong>Emergencia:</strong> 24 horas
                </li>
              </ul>
            </article>
          </div>
        </div>
      </section>
    </main>
  );
}

export function SegurosPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover
        title="Convenios y seguros"
        subtitle="Trabajamos con aseguradoras y convenios corporativos (editable según tus acuerdos)."
      />
      <section className="section section--alt section--after-cover">
        <div className="container">
          <div className="logos" aria-label="Aseguradoras (demo)">
            <div className="logo">Seguro A</div>
            <div className="logo">Seguro B</div>
            <div className="logo">Seguro C</div>
            <div className="logo">Seguro D</div>
            <div className="logo">Seguro E</div>
          </div>
        </div>
      </section>
    </main>
  );
}

export function BlogPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover
        title="Consejos de salud"
        subtitle="Educación para pacientes: prevención, hábitos y señales de alerta."
      />
      <section className="section section--after-cover">
        <div className="container">
          <div className="grid grid--3">
            <article className="post">
              <p className="post__tag">Cardio</p>
              <h3 className="post__title">5 señales para chequear tu corazón</h3>
              <p className="post__excerpt">Identifica síntomas y cuándo acudir a consulta médica.</p>
              <Link className="post__link" to="/contacto">
                Leer más
              </Link>
            </article>
            <article className="post">
              <p className="post__tag">Pediatría</p>
              <h3 className="post__title">Vacunas: calendario y recomendaciones</h3>
              <p className="post__excerpt">Guía rápida para mantener el esquema al día.</p>
              <Link className="post__link" to="/contacto">
                Leer más
              </Link>
            </article>
            <article className="post">
              <p className="post__tag">Bienestar</p>
              <h3 className="post__title">Sueño y salud: hábitos que sí funcionan</h3>
              <p className="post__excerpt">Rutinas simples para dormir mejor y rendir más.</p>
              <Link className="post__link" to="/contacto">
                Leer más
              </Link>
            </article>
          </div>
        </div>
      </section>
    </main>
  );
}

export function ManualPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover
        title="Guía del paciente y políticas"
        subtitle="Derechos del paciente, consentimientos informados, protección de datos personales y lineamientos de atención en Clínica NovaSalud."
      />
      <section className="section section--after-cover" style={{ paddingBottom: '40px' }}>
        <div className="container" style={{ maxWidth: '820px' }}>
          <article className="card card--soft" style={{ marginBottom: '16px' }}>
            <h2 className="location__title" style={{ marginTop: 0 }}>
              Derechos del paciente
            </h2>
            <p style={{ margin: '0 0 12px', lineHeight: 1.65 }}>
              Toda persona tiene derecho a una atención digna, oportuna y segura; a conocer su diagnóstico y
              alternativas de tratamiento en términos comprensibles; a decidir de manera libre e informada; y a la
              confidencialidad de su información de salud, salvo obligaciones legales de notificación.
            </p>
            <ul className="list" style={{ margin: 0 }}>
              <li>Recibir identificación del personal que le atiende y del médico responsable.</li>
              <li>Solicitar segunda opinión médica cuando corresponda según normativa aplicable.</li>
              <li>Presentar quejas y reclamos por canales institucionales sin que ello afecte la continuidad de la
                atención de urgencia.</li>
            </ul>
          </article>

          <article className="card card--soft" style={{ marginBottom: '16px' }}>
            <h2 className="location__title" style={{ marginTop: 0 }}>
              Consentimiento informado
            </h2>
            <p style={{ margin: 0, lineHeight: 1.65 }}>
              Antes de procedimientos invasivos, anestesia, cirugía o participación en docencia/investigación, se le
              entregará información sobre riesgos, beneficios y alternativas. Deberá firmar el consentimiento cuando
              esté de acuerdo; puede retirar el consentimiento según las reglas clínicas vigentes.
            </p>
          </article>

          <article className="card card--soft" style={{ marginBottom: '16px' }}>
            <h2 className="location__title" style={{ marginTop: 0 }}>
              Privacidad y datos personales
            </h2>
            <p style={{ margin: '0 0 12px', lineHeight: 1.65 }}>
              Los datos de salud se tratan con medidas técnicas y organizativas razonables, acceso restringido al
              personal autorizado y finalidades vinculadas a la atención, facturación, calidad y obligaciones legales.
              Puede ejercer derechos de acceso, rectificación y otros según la Ley N.º 29733 y su reglamento, acercándose
              a admisión o al correo de contacto institucional.
            </p>
          </article>

          <article className="card card--soft" style={{ marginBottom: '16px' }}>
            <h2 className="location__title" style={{ marginTop: 0 }}>
              Uso del sitio web y citas
            </h2>
            <p style={{ margin: 0, lineHeight: 1.65 }}>
              La agenda en línea confirma disponibilidad orientativa; la institución puede reprogramar por fuerza mayor
              o criterio clínico. Los mensajes automáticos no sustituyen la indicación médica escrita.
            </p>
          </article>

          <p className="muted" style={{ margin: 0, lineHeight: 1.55 }}>
            Puede ampliar esta sección con documentos revisados por asesoría legal. Para solicitar copias
            oficiales, comuníquese con la institución.
          </p>
        </div>
      </section>
    </main>
  );
}

export function ContactoPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover
        title="Contáctenos"
        subtitle="Mesa de ayuda para citas, informes administrativos y orientación general. Para urgencias vitales use emergencias o el número local (105 / 116 según corresponda)."
      />
      <section className="section section--cta section--after-cover" style={{ paddingBottom: '40px' }}>
        <div className="container">
          <div className="cta">
            <div className="cta__copy">
              <h2 className="cta__title">Canales de atención</h2>
              <p className="cta__text">
                Para citas web use el botón de agenda; para consultas urgentes no médicas puede llamar a la central
                durante el horario indicado más abajo.
              </p>
              <div className="cta__actions">
                <Link className="btn btn--primary" to="/cita">
                  Agendar cita
                </Link>
                <a className="btn btn--ghost" href="tel:+51011234567">
                  Llamar
                </a>
              </div>
            </div>
          </div>

          <div className="grid grid--2" style={{ marginTop: '28px' }}>
            <article className="card card--soft">
              <h2 className="location__title" style={{ marginTop: 0 }}>
                Datos de contacto
              </h2>
              <ul className="list" style={{ margin: 0 }}>
                <li>
                  <strong>Dirección principal:</strong> {CLINIC.address}
                </li>
                <li>
                  <strong>Central telefónica:</strong>{' '}
                  <a className="card__link" href="tel:+51011234567">
                    (01) 123-4567
                  </a>
                </li>
                <li>
                  <strong>Correo:</strong>{' '}
                  <a className="card__link" href="mailto:contacto@novasalud.pe">
                    contacto@novasalud.pe
                  </a>
                </li>
                <li>
                  <strong>Horario de mesa de partes / información:</strong> lunes a viernes 8:00–18:00; sábados
                  8:00–13:00
                </li>
              </ul>
            </article>
            <article className="card card--soft">
              <h2 className="location__title" style={{ marginTop: 0 }}>
                Trámite documentario
              </h2>
              <p style={{ margin: '0 0 10px', lineHeight: 1.65 }}>
                Para cartas notariales, certificados de atención o documentación institucional, indique número de
                historia, fecha aproximada de atención y copia de DNI del paciente o apoderado.
              </p>
              <p style={{ margin: 0, lineHeight: 1.65 }}>
                Tiempo de respuesta orientativo: 3 a 5 días hábiles según complejidad. Consultas generales también por
                el formulario de cita si marca la opción de consulta no médica cuando esté disponible.
              </p>
            </article>
          </div>
        </div>
      </section>
    </main>
  );
}
