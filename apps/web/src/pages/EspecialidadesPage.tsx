import { CatalogEspecialidades } from '../components/CatalogGrids';
import PageCover from '../components/PageCover';

export default function EspecialidadesPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover
        title="Especialidades"
        subtitle="Catálogo en vivo desde Laravel. Los cambios en el administrador se reflejan al recargar."
      />
      <section className="section section--after-cover">
        <div className="container">
          <CatalogEspecialidades />
        </div>
      </section>
    </main>
  );
}
