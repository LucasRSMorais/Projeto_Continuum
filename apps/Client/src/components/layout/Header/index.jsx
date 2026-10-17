import { HeaderContainer, Logo, Nav, NavLink, Dropdown, MenuSection, MenuItem, Badge } from './styles';

import { useAuth } from '../../../services/utils/auth';
import humanIcon from '../../../assets/icons/human.svg';
import downloadIcon from '../../../assets/icons/download.svg';
import handoffIcon from '../../../assets/icons/handoff.svg';
import ColormodeIcon from '../../../assets/icons/color-mode.svg';
import notificationIcon from '../../../assets/icons/notification.svg';
import questionIcon from '../../../assets/icons/question.svg';
import settingsIcon from '../../../assets/icons/setting.svg';
import logoutIcon from '../../../assets/icons/logout.svg';
import { useOutsideClick } from '../../../hooks/useOutsideClick';
import handIcon from '../../../assets/icons/hand.svg';
function Header() {
  const { isOpen, setIsOpen, ref } = useOutsideClick(false);
  const  {user} = useAuth();
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
                <NavLink href="/download" className="nav-link" title="Download">
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

                        <MenuItem>
                         <img src={questionIcon} alt="" className="icon" />
                          <span>Loreminpus</span>
                        </MenuItem>
                        <MenuItem>
                          <img src={questionIcon} alt="" className="icon" />
                          <span>LorenIpsum</span>
                        </MenuItem>
                        <MenuItem>
                          <img src={questionIcon} alt="" className="icon" /> 
                          <span>LorenIpsum</span>
                        </MenuItem>
                      </ul>
                    </MenuSection>
                    
                    <MenuSection>
                      <span className="section-title">Configurações</span>
                      <ul>
                        <MenuItem>
                          <img src={humanIcon} alt="" className="icon" /> 
                          <span>Perfil</span>
                        </MenuItem>
                        <MenuItem>
                          <img src={settingsIcon} alt="" className="icon" /> 
                          <span>Preferências</span>
                        </MenuItem>

                        <MenuItem >
                          <img src={logoutIcon} alt="" className="icon" /> 
                          <span>  <a href="/">Logout</a>   </span>
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
 
export default Header;