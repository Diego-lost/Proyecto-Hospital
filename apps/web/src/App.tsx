import { Route, Routes } from 'react-router-dom';
import SiteLayout from './components/SiteLayout';
import HomePage from './pages/HomePage';
import EspecialidadesPage from './pages/EspecialidadesPage';
import SolicitudPage from './pages/SolicitudPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import ForgotPasswordPage from './pages/ForgotPasswordPage';
import ResetPasswordPage from './pages/ResetPasswordPage';
import {
  BlogPage,
  ContactoPage,
  EquipoPage,
  ManualPage,
  SedesPage,
  SegurosPage,
} from './pages/StaticPages';
import PagoPage from './pages/PagoPage';
import { PagoCanceladoPage, PagoExitoPage, PagoRegistradoPage } from './pages/PagoResultPages';
import PortalHubPage from './pages/portal/PortalHubPage';
import SeccionPortalPage from './pages/portal/SeccionPortalPage';

export default function App() {
  return (
    <Routes>
      <Route element={<SiteLayout />}>
        <Route path="/" element={<HomePage />} />
        <Route path="/especialidades" element={<EspecialidadesPage />} />
        <Route path="/equipo" element={<EquipoPage />} />
        <Route path="/sedes" element={<SedesPage />} />
        <Route path="/seguros" element={<SegurosPage />} />
        <Route path="/blog" element={<BlogPage />} />
        <Route path="/manual-politicas" element={<ManualPage />} />
        <Route path="/contacto" element={<ContactoPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/registro" element={<RegisterPage />} />
        <Route path="/recuperar-contrasena" element={<ForgotPasswordPage />} />
        <Route path="/restablecer-contrasena" element={<ResetPasswordPage />} />
        <Route path="/cita" element={<SolicitudPage />} />
        <Route path="/pagar" element={<PagoPage />} />
        <Route path="/pagar/:servicioId" element={<PagoPage />} />
        <Route path="/pago/exito" element={<PagoExitoPage />} />
        <Route path="/pago/cancelado" element={<PagoCanceladoPage />} />
        <Route path="/pago/registrado" element={<PagoRegistradoPage />} />
        <Route path="/institucional" element={<PortalHubPage seccion="institucional" />} />
        <Route path="/institucional/:slug" element={<SeccionPortalPage seccion="institucional" />} />
        <Route path="/organizacion" element={<PortalHubPage seccion="organizacion" />} />
        <Route path="/organizacion/:slug" element={<SeccionPortalPage seccion="organizacion" />} />
        <Route path="/prensa" element={<PortalHubPage seccion="prensa" />} />
        <Route path="/prensa/:slug" element={<SeccionPortalPage seccion="prensa" />} />
        <Route path="/atencion" element={<PortalHubPage seccion="atencion" />} />
        <Route path="/atencion/:slug" element={<SeccionPortalPage seccion="atencion" />} />
      </Route>
    </Routes>
  );
}
