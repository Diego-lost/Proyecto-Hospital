import { publicAsset } from '../../lib/publicAsset';

/** Imágenes locales de la home (`public/img/hero`). */
export const HOME_SECTION_IMAGES = {
  stats: publicAsset('img/hero/slide-equipo.jpg'),
  appointmentBanner: publicAsset('img/hero/Reserva tu consulta.jpg'),
  paymentBanner: publicAsset('img/hero/Pago servicios.jpg'),
  specialtiesBanner: publicAsset('img/hero/Catalogo Especialidades.jpg'),
  articlesBanner: publicAsset('img/hero/Articulos Clinicos.jpg'),
} as const;

export { specialtyImageUrl } from '../../lib/specialtyImages';