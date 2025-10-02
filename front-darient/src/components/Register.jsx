import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080';

export default function Register() {
  const [name, setName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [password_confirmation, setPasswordConfirmation] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const navigate = useNavigate();

  const handleSubmit = (e) => {
    e.preventDefault(); // Evita el comportamiento por defecto del formulario

    // Validación básica
    if (!name || !lastName || !email || !password) {
      setErrorMessage('Por favor, completa todos los campos.');
      return;
    }

    // Datos a enviar
    const formData = {
      name,
      lastName,
      email,
      password,
      password_confirmation,
    };

    // Enviar datos al backend
    fetch(`${API_URL}/api/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Error al registrar el usuario.');
        }
        return response.json();
      })
      .then((data) => {
        setErrorMessage('¡Registro exitoso!');
        alert('¡Registro exitoso!');
        // Limpiar campos
        setName('');
        setLastName('');
        setEmail('');
        setPassword('');
        setPasswordConfirmation('');
        navigate('/tasks');
      })
      .catch((error) => {
        console.error('Error:', error);
        setErrorMessage('Hubo un error al registrar el usuario.');
      });
  };

  return (
    <div className="container mx-auto p-6">
      <h2 className="text-2xl font-bold mb-4 text-gray-700"  >Registro</h2>
      <form onSubmit={handleSubmit} className="bg-white p-6 rounded shadow-md">
        <div className="mb-4">
          <label className="block text-gray-700 mb-2">Nombre</label>
          <input
            type="text"
            value={name}
            name="name"
            onChange={(e) => setName(e.target.value)}
            className="w-full p-2 border rounded text-gray-700 "
            required
          />
        </div>
        <div className="mb-4">
          <label className="block text-gray-700 mb-2">Apellido</label>
          <input
            type="text"
            value={lastName}
            name="lastName"
            onChange={(e) => setLastName(e.target.value)}
            className="w-full p-2 border rounded text-gray-700 "
            required
          />
        </div>
        <div className="mb-4">
          <label className="block text-gray-700 mb-2">Email</label>
          <input
            type="email"
            value={email}
            name="email"
            onChange={(e) => setEmail(e.target.value)}
            className="w-full p-2 border rounded text-gray-700 "
            required
          />
        </div>
        <div className="mb-4">
          <label className="block text-gray-700 mb-2">Contraseña</label>
          <input
            type="password"
            name="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="w-full p-2 border rounded text-gray-700 "
            required
          />
        </div>
        <div className="mb-4">
            <label className="block text-gray-700 mb-2">Confirmar Contraseña</label>
            <input
              type="password"
              name="password_confirmation"
              value={password_confirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              className="w-full p-2 border rounded text-gray-700"
              required
            />
          </div>

        <button
          type="submit"
          className="bg-blue-600 text-white p-2 rounded hover:bg-blue-700"
        >
          Registrar
        </button>
      </form>
    </div>
  );
}