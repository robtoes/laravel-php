import { useAuth } from '../context/AuthContext';

export default function Logout() {
  const { logout } = useAuth();

  const handleLogout = () => {
    logout();
    window.location.href = '/login';
  };

  return (
    <button
      onClick={handleLogout}
      className="bg-red-500 text-white p-2 rounded hover:bg-red-600"
    >
      Cerrar Sesión
    </button>
  );
}