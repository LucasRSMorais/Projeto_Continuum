import { HeaderContainer, HeaderActions, Logo, Nav, NavLink, Dropdown, MenuSection, MenuItem } from './styles';
import { useAuth } from '../../../../services/utils/auth';
import humanIcon from '../../../../assets/icons/human.svg';
import downloadIcon from '../../../../assets/icons/download.svg';
import ColormodeIcon from '../../../../assets/icons/color-mode.svg';
import notificationIcon from '../../../../assets/icons/notification.svg';
import settingsIcon from '../../../../assets/icons/setting.svg';
import logoutIcon from '../../../../assets/icons/logout.svg';
import { useOutsideClick } from '../../../../hooks/useOutsideClick';
import handIcon from '../../../../assets/icons/hand.svg';
import { buildApiUrl } from '../../../../config/api';
import HeaderButton from '../../../Buttons/HeaderButton';
import { FcPrivacy } from "react-icons/fc";
import { FcVoicePresentation } from "react-icons/fc";

function HeaderPaciente() {
  const { isOpen, setIsOpen, ref } = useOutsideClick(false);
  const { user } = useAuth();

  const exporta = () => {
    const email = user?.email;

    if (!email) {
      alert('Não foi possível identificar');
      return;
    }

    window.location.href = `${buildApiUrl('exportar.php')}?email=${encodeURIComponent(user?.email)}`;
  };

  return (
    <HeaderContainer>
      <div>
        <Logo src="/nome_sfundo_cpr.svg" alt="Logo" />
      </div>

      <Nav>
        <ul>
          <li>
            <HeaderActions>
              {/* Os destinos podem ser atualizados quando as páginas forem definidas. */}
              <HeaderButton to="/exames">Exames</HeaderButton>
              <HeaderButton to="/home_paciente">Inicio</HeaderButton>
            </HeaderActions>
          </li>
          <li>
            <NavLink href="/" className="nav-link" title="Alertas">
              <img src={notificationIcon} alt="" />
            </NavLink>
          </li>
          <li>
            <NavLink href="/" className="nav-link" title="Alterar Tema">
              <img src={ColormodeIcon} alt="" />
            </NavLink>
          </li>
          <li>
            <NavLink className="nav-link" title="Exporta os meus dados" onClick={exporta}>
              <img src={downloadIcon} alt="" />
            </NavLink>
          </li>
          <li ref={ref} style={{ position: 'relative' }}>
            <div
              className="nav-link"
              title="Perfil"
              onClick={() => setIsOpen(!isOpen)}
              style={{ cursor: 'pointer', display: 'flex', alignItems: 'center' }}
            >
              <img src={humanIcon} alt="" />
            </div>

            {isOpen && (
              <Dropdown>
                <MenuSection>
                  <span className="section-title">Usuário</span>
                  <ul>
                      <MenuItem>
                      <FcVoicePresentation alt="" className="icon" />
                      <span>Olá, {user?.nome || 'Usuário'}</span>
                    </MenuItem>
                  </ul>
                </MenuSection>
                <MenuSection>
                  <span className="section-title">Conta</span>
                  <ul>
                    <MenuItem>
                      <img src={humanIcon} alt="" className="icon" />
                      <span><a href="/perfilpaciente">Perfil</a></span>
                    </MenuItem> 
                  </ul>
                </MenuSection>
                <MenuSection>
                  <span className="section-title">Configurações</span>
                  <ul>
                    <MenuItem>
                      <img src={settingsIcon} alt="" className="icon" />
                      <span><a href="/configuracao">Preferências</a></span>
                    </MenuItem>
                    <MenuItem>
                      <FcPrivacy alt="" className="icon" />
                      <span><a href="/privacidade">Privacidade</a></span>
                    </MenuItem>
                    <MenuItem>
                      <img src={logoutIcon} alt="" className="icon" />
                      <span><a href="/login_pacientes">Logout</a></span>
                    </MenuItem>
                  </ul>
                </MenuSection>
              </Dropdown>
            )}
          </li>
        </ul>
      </Nav>
    </HeaderContainer>
  );
}

export default HeaderPaciente;