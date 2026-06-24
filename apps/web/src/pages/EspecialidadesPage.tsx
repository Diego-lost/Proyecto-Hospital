import { CatalogEspecialidades } from '../components/CatalogGrids';
import PageCover from '../components/PageCover';

export default function EspecialidadesPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover
        title="Especialidades"
        subtitle="Consulta nuestras especialidades y agenda la atención que necesitas."
      />
      <section className="section section--after-cover">
        <div className="container">
          <CatalogEspecialidades />
        </div>
      </section>
    </main>
  );
}
