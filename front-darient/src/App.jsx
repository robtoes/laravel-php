import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
//import Navbar from './components/Navbar';
import Register from './components/Register';
import Login from './components/Login';
import Tasks from './components/Tasks';
import PrivateRoute from './components/PrivateRoute';
import Logout from './components/Logout';

import { Menu, X, Home, LogIn, UserPlus, User, LogOut, Settings, List } from 'lucide-react';
import { useState } from 'react';
import { useAuth } from './context/AuthContext';


const NavBar = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const { isAuthenticated, user, logout } = useAuth();
  
  const token = localStorage.getItem('token');

  if(token==null){
    localStorage.setItem('token', "");
  }

  const lengthToken = token.length > 20 ? token.length  : 0;

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen);
  };

  function handleLogout(){
    logout();
    window.location.href = '/login';
  }

  return (
    <nav className="bg-white shadow-lg border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16">
          <div className="flex items-center">
            <div className="flex-shrink-0 flex items-center">
              <div className="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-xl">T</span>
              </div>
              <span className="ml-3 text-xl font-bold text-gray-900">TaskApp</span>
            </div>
          </div>

          <div className="hidden md:flex md:items-center md:space-x-4">
            <a
              href="#"
              className="flex items-center gap-2 px-4 py-2 text-gray-700 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
            >
              <Home className="w-5 h-5" />
              Inicio
            </a>

            {lengthToken == 0 && (
              <>
                <a
                  href="/login"
                  className="flex items-center gap-2 px-4 py-2 text-gray-700 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
                >
                  <LogIn className="w-5 h-5" />
                  Iniciar Sesión
                </a>
                <a
                  href="/register"
                  className="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white hover:bg-purple-700 rounded-lg transition font-medium"
                >
                  <UserPlus className="w-5 h-5" />
                  Registrarse
                </a>
              </>
            )}

            {lengthToken > 10 && (
              <>
                <a
                  href="#"
                  className="flex items-center gap-2 px-4 py-2 text-gray-700 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
                >
                  <List className="w-5 h-5" />
                  Mis Tareas
                </a>
                

                <div className="relative group">
                      <button
                        onClick={handleLogout}
                        className="w-full flex items-center gap-3 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                      >
                        <LogOut className="w-4 h-4" />
                        Cerrar Sesión
                      </button>
                    </div>
              </>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
};

function App() {
  //const { isAuthenticated, login } = useAuth();

  return (
    <AuthProvider>
    <Router>
      <div className="min-h-screen bg-gray-100 " >
        <NavBar />
        <Routes>
          <Route path="/register" element={<Register />} />
          <Route path="/login" element={<Login />} />
          <Route path="/tasks" element={
          <PrivateRoute>
            <Tasks />
          </PrivateRoute>
          } />
          <Route path="/logout" element={<Logout />} />
          
          <Route path="*" element={<h1>Página no encontrada</h1>} />
        </Routes>
      </div>
    </Router>
    </AuthProvider>
  );
}

export default App;
