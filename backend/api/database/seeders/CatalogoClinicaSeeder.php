<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Servicio;
use Illuminate\Database\Seeder;

class CatalogoClinicaSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            'Medicina General' => [
                'medicos' => [
                    [
                        'nombre' => 'Dr. Carlos Mendoza López',
                        'dni' => '45678901',
                        'servicios' => [
                            ['nombre' => 'Consulta medicina general', 'descripcion' => 'Evaluación clínica integral, diagnóstico y plan de tratamiento.', 'precio' => 60.00],
                            ['nombre' => 'Control de salud', 'descripcion' => 'Seguimiento de enfermedades crónicas y controles periódicos.', 'precio' => 45.00],
                            ['nombre' => 'Certificado médico', 'descripcion' => 'Emisión de certificado médico para trámites laborales o académicos.', 'precio' => 35.00],
                        ],
                    ],
                    [
                        'nombre' => 'Dra. Rosa Quispe Vargas',
                        'dni' => '45678902',
                        'servicios' => [
                            ['nombre' => 'Consulta general adultos', 'descripcion' => 'Atención ambulatoria para pacientes adultos.', 'precio' => 60.00],
                            ['nombre' => 'Evaluación preoperatoria', 'descripcion' => 'Valoración médica previa a procedimientos quirúrgicos.', 'precio' => 80.00],
                        ],
                    ],
                ],
            ],
            'Cardiología' => [
                'medicos' => [
                    [
                        'nombre' => 'Dr. Miguel Torres Silva',
                        'dni' => '23456789',
                        'servicios' => [
                            ['nombre' => 'Consulta cardiológica', 'descripcion' => 'Evaluación de enfermedades del corazón y factores de riesgo cardiovascular.', 'precio' => 120.00],
                            ['nombre' => 'Electrocardiograma', 'descripcion' => 'Registro de la actividad eléctrica del corazón con interpretación médica.', 'precio' => 80.00],
                            ['nombre' => 'Ecocardiograma', 'descripcion' => 'Estudio ecográfico del corazón para valorar estructura y función.', 'precio' => 180.00],
                        ],
                    ],
                    [
                        'nombre' => 'Dra. Ana Lucía Ramos Paredes',
                        'dni' => '23456790',
                        'servicios' => [
                            ['nombre' => 'Consulta cardiológica de control', 'descripcion' => 'Seguimiento de hipertensión, arritmias y cardiopatías.', 'precio' => 100.00],
                            ['nombre' => 'Holter de presión arterial', 'descripcion' => 'Monitoreo ambulatorio de presión arterial por 24 horas.', 'precio' => 150.00],
                        ],
                    ],
                ],
            ],
            'Pediatría' => [
                'medicos' => [
                    [
                        'nombre' => 'Dra. Patricia Huamán Soto',
                        'dni' => '34567890',
                        'servicios' => [
                            ['nombre' => 'Consulta pediátrica', 'descripcion' => 'Atención médica para niños y adolescentes.', 'precio' => 90.00],
                            ['nombre' => 'Control de crecimiento', 'descripcion' => 'Evaluación de talla, peso y desarrollo infantil.', 'precio' => 70.00],
                            ['nombre' => 'Vacunación y orientación', 'descripcion' => 'Asesoría sobre esquema de vacunación y prevención.', 'precio' => 50.00],
                        ],
                    ],
                    [
                        'nombre' => 'Dr. Jorge Vega Castro',
                        'dni' => '34567891',
                        'servicios' => [
                            ['nombre' => 'Consulta pediátrica de urgencia', 'descripcion' => 'Atención prioritaria por fiebre, dolor o malestar agudo.', 'precio' => 110.00],
                            ['nombre' => 'Control neonatal', 'descripcion' => 'Seguimiento del recién nacido durante el primer mes de vida.', 'precio' => 85.00],
                        ],
                    ],
                ],
            ],
            'Traumatología y Ortopedia' => [
                'medicos' => [
                    [
                        'nombre' => 'Dr. Luis Ramírez Paredes',
                        'dni' => '56789012',
                        'servicios' => [
                            ['nombre' => 'Consulta traumatológica', 'descripcion' => 'Evaluación de lesiones óseas, articulares y musculares.', 'precio' => 100.00],
                            ['nombre' => 'Infiltración articular', 'descripcion' => 'Procedimiento para alivio de dolor e inflamación articular.', 'precio' => 130.00],
                            ['nombre' => 'Curación y retiro de puntos', 'descripcion' => 'Curación de heridas y retiro de suturas postquirúrgicas.', 'precio' => 40.00],
                        ],
                    ],
                    [
                        'nombre' => 'Dr. Fernando Díaz Morales',
                        'dni' => '56789013',
                        'servicios' => [
                            ['nombre' => 'Consulta ortopédica', 'descripcion' => 'Valoración de fracturas, luxaciones y patología de columna.', 'precio' => 100.00],
                            ['nombre' => 'Férula y inmovilización', 'descripcion' => 'Colocación de férula provisional por trauma leve.', 'precio' => 75.00],
                        ],
                    ],
                ],
            ],
            'Ginecología y Obstetricia' => [
                'medicos' => [
                    [
                        'nombre' => 'Dra. María Elena Flores Ríos',
                        'dni' => '67890123',
                        'servicios' => [
                            ['nombre' => 'Consulta ginecológica', 'descripcion' => 'Control ginecológico, PAP y orientación en salud femenina.', 'precio' => 95.00],
                            ['nombre' => 'Control prenatal', 'descripcion' => 'Seguimiento del embarazo y evaluación materno-fetal.', 'precio' => 90.00],
                            ['nombre' => 'Ecografía obstétrica', 'descripcion' => 'Ecografía para seguimiento del embarazo.', 'precio' => 120.00],
                        ],
                    ],
                    [
                        'nombre' => 'Dra. Carmen Salazar Mendoza',
                        'dni' => '67890124',
                        'servicios' => [
                            ['nombre' => 'Consulta ginecológica de control', 'descripcion' => 'Control de patologías ginecológicas y planificación familiar.', 'precio' => 90.00],
                            ['nombre' => 'Colocación de DIU', 'descripcion' => 'Procedimiento de inserción de dispositivo intrauterino.', 'precio' => 150.00],
                        ],
                    ],
                ],
            ],
            'Dermatología' => [
                'medicos' => [
                    [
                        'nombre' => 'Dr. Ricardo Ponce Luna',
                        'dni' => '78901234',
                        'servicios' => [
                            ['nombre' => 'Consulta dermatológica', 'descripcion' => 'Diagnóstico y tratamiento de afecciones de piel, cabello y uñas.', 'precio' => 95.00],
                            ['nombre' => 'Crioterapia de verrugas', 'descripcion' => 'Tratamiento de verrugas y lesiones benignas con nitrógeno líquido.', 'precio' => 60.00],
                        ],
                    ],
                ],
            ],
            'Odontología' => [
                'medicos' => [
                    [
                        'nombre' => 'Dr. Jorge Castillo Navarro',
                        'dni' => '89012345',
                        'servicios' => [
                            ['nombre' => 'Consulta odontológica', 'descripcion' => 'Evaluación bucal, diagnóstico y plan de tratamiento dental.', 'precio' => 50.00],
                            ['nombre' => 'Limpieza dental', 'descripcion' => 'Profilaxis y remoción de placa bacteriana y sarro.', 'precio' => 80.00],
                            ['nombre' => 'Extracción dental simple', 'descripcion' => 'Extracción de pieza dental sin complicaciones.', 'precio' => 70.00],
                        ],
                    ],
                    [
                        'nombre' => 'Dra. Silvia Núñez Ortiz',
                        'dni' => '89012346',
                        'servicios' => [
                            ['nombre' => 'Obturación dental', 'descripcion' => 'Restauración de caries con resina o amalgama.', 'precio' => 90.00],
                            ['nombre' => 'Blanqueamiento dental', 'descripcion' => 'Tratamiento estético para aclarar el color de los dientes.', 'precio' => 250.00],
                        ],
                    ],
                ],
            ],
            'Oftalmología' => [
                'medicos' => [
                    [
                        'nombre' => 'Dra. Lucía Campos Herrera',
                        'dni' => '90123456',
                        'servicios' => [
                            ['nombre' => 'Consulta oftalmológica', 'descripcion' => 'Evaluación de la visión y enfermedades oculares.', 'precio' => 90.00],
                            ['nombre' => 'Examen de fondo de ojo', 'descripcion' => 'Evaluación de retina y nervio óptico.', 'precio' => 70.00],
                        ],
                    ],
                ],
            ],
            'Neurología' => [
                'medicos' => [
                    [
                        'nombre' => 'Dr. Pedro Aguilar Vela',
                        'dni' => '01234567',
                        'servicios' => [
                            ['nombre' => 'Consulta neurológica', 'descripcion' => 'Evaluación de cefalea, mareos, convulsiones y otras patologías neurológicas.', 'precio' => 120.00],
                            ['nombre' => 'Electroencefalograma', 'descripcion' => 'Estudio de actividad eléctrica cerebral con informe médico.', 'precio' => 150.00],
                        ],
                    ],
                ],
            ],
            'Psiquiatría' => [
                'medicos' => [
                    [
                        'nombre' => 'Dra. Elena Montoya García',
                        'dni' => '12345670',
                        'servicios' => [
                            ['nombre' => 'Consulta psiquiátrica', 'descripcion' => 'Evaluación y tratamiento de ansiedad, depresión y otros trastornos.', 'precio' => 110.00],
                            ['nombre' => 'Consulta psiquiátrica de control', 'descripcion' => 'Seguimiento de tratamiento farmacológico y psicoterapéutico.', 'precio' => 90.00],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($catalogo as $nombreEspecialidad => $config) {
            $especialidad = Especialidad::query()->firstOrCreate(
                ['nombre' => $nombreEspecialidad],
            );

            foreach ($config['medicos'] as $medicoData) {
                $medico = Medico::query()->firstOrCreate(
                    ['dni' => $medicoData['dni']],
                    [
                        'nombre' => $medicoData['nombre'],
                        'especialidad_id' => $especialidad->id,
                    ],
                );

                if ($medico->especialidad_id !== $especialidad->id) {
                    $medico->update(['especialidad_id' => $especialidad->id]);
                }

                if ($medico->nombre !== $medicoData['nombre']) {
                    $medico->update(['nombre' => $medicoData['nombre']]);
                }

                foreach ($medicoData['servicios'] as $servicioData) {
                    Servicio::query()->firstOrCreate(
                        [
                            'nombre' => $servicioData['nombre'],
                            'medico_id' => $medico->id,
                        ],
                        [
                            'descripcion' => $servicioData['descripcion'],
                            'precio' => $servicioData['precio'],
                        ],
                    );
                }
            }
        }
    }
}
