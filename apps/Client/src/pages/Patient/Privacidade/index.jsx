import HeaderPaciente from '../../../components/layout/Patient/HeaderPaciente';
import Footer from '../../../components/layout/Footer';
import MainPrivacity from '../../../components/layout/Patient/Mains/MainPrivacity';

function Privacidade() {
  return (
    <>
      <HeaderPaciente />
      {/* <iframe
        title="Política de Privacidade"
        src="/politica_privacidade.pdf"
        style={{ width: '100%', height: '80vh', border: 0 }}
      /> */}
      <MainPrivacity />
      <Footer />
    </>
  );
}

export default Privacidade;