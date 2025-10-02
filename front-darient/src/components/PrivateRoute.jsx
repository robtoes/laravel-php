import { Navigate, useLocation } from 'react-router-dom';
import { useContext } from 'react';
import { useAuth } from '../context/AuthContext';

export default function PrivateRoute({ children }) {
  const location = useLocation();
  const token = localStorage.getItem('token');

  if (token=="null") {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return children;
}