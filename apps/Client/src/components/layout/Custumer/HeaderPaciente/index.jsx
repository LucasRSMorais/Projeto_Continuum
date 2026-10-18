import { HeaderContainer, Logo, Nav, NavLink, Dropdown, MenuSection, MenuItem } from './styles';

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
function HeaderPaciente() {
  const { isOpen, setIsOpen, ref } = useOutsideClick(false);
  const  {user} = useAuth();
  const exporta = () =>{

  const email = user?.email ;


if (!email){
  alert ("Não foi possivel identificar");
  return
}



window.location.href = `${buildApiUrl('exportar.php')}?email=${encodeURIComponent(user?.email)} `;


  };
  return (
    <HeaderContainer>
        <div>
            <Logo src="/nome_sfundo_cpr.svg" alt="Logo" />
        </div>
        <Nav>
            <ul>
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
                <NavLink  className="nav-link" title="Exporta os meus dados" onClick = {exporta}  >
                  <img src={downloadIcon} alt="" />
                </NavLink>
              </li>
              {/* O ref é aplicado ao elemento <li> que engloba o ícone de perfil e o menu */}
              <li ref={ref} style={{ position: 'relative' }}>
                {/* O NavLink foi substituído por uma div com onClick para evitar o comportamento de hiperligação padrão */}
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
                      <span className="section-title">Conta</span>
                      <ul>

                           <MenuItem>
                         <img src={handIcon} alt="" className="icon" />
                          <span>Olá  {user?.nome || "Usuario"}  </span>
                        </MenuItem>

                      </ul>
                    </MenuSection>
                    
                    <MenuSection>
                      <span className="section-title">Configurações</span>
                      <ul>
                        <MenuItem>
                          <img src={humanIcon} alt="" className="icon" /> 
                          <span><a href="/configuracao">Perfil</a></span>
                        </MenuItem>
                        <MenuItem>
                          <img src={settingsIcon} alt="" className="icon" /> 
                          <span><a href="/configuracao">Preferências</a></span>
                        </MenuItem>

                        <MenuItem >
                          <img src={logoutIcon} alt="" className="icon" /> 
                          <span>  <a href="/login_pacientes">Logout</a>   </span>
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