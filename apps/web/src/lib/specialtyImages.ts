import { publicAsset } from './publicAsset';

const SPECIALTY_IMAGE_FILES: Record<string, string> = {
  'medicina general': 'Medicina General.jpg',
  cardiologia: 'Cardiologia.jpg',
  dermatologia: 'Dermatologia.jpg',
  'ginecologia y obstetricia': 'Ginecología y Obstetricia.jpg',
  neurologia: 'Neurología.jpg',
  odontologia: 'Odontología.jpg',
  oftalmologia: 'Oftalmología.jpg',
  pediatria: 'Pediatría.jpg',
  psicologia: 'PSICOLOGIA.jpg',
  psiquiatria: 'Psiquiatría.jpg',
  'traumatologia y ortopedia': 'Traumatología y Ortopedia.jpg',
};

export function normalizeSpecialtyName(name: string): string {
  return name
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

export function specialtyImageUrl(name: string): string {
  const file = SPECIALTY_IMAGE_FILES[normalizeSpecialtyName(name)];
  return publicAsset(file ? `img/hero/${file}` : 'img/hero/Catalogo Especialidades.jpg');
}
