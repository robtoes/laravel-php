import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import TaskForm from './TaskForm.jsx';
import { X, Plus, CheckCircle, Trash2, Edit } from 'lucide-react';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080';

const TaskCard = ({ task, onEdit, onDelete }) => {
  return (
    <div className="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
      <div className="flex items-start justify-between mb-3">
        <h3 className="text-lg font-semibold text-gray-800 flex-1">{task.title}</h3>
        <div className="flex gap-2">
          <button
            onClick={() => onEdit(task)}
            className="text-blue-600 hover:text-blue-700 hover:bg-blue-50 p-2 rounded-lg transition"
            title="Editar"
          >
            <Edit className="w-4 h-4" />
          </button>
          <button
            onClick={() => onDelete(task.id)}
            className="text-red-600 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition"
            title="Eliminar"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      </div>
      <p className="text-gray-600 text-sm leading-relaxed">{task.description}</p>
      <div className="mt-4 pt-4 border-t border-gray-100">
        <span className="text-xs text-gray-500">
          Creado: {new Date(task.created_at).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          })}
        </span>
      </div>
    </div>
  );
};


const Tasks = () => {
  const token = localStorage.getItem('token');
  const navigate = useNavigate();
  const [tasks, setTasks] = useState([]);

  /*const [tasks, setTasks] = useState([
    {
      id: 1,
      titulo: 'Implementar autenticación JWT',
      descripcion: 'Configurar Laravel Sanctum con tokens JWT para la autenticación de usuarios en la aplicación.',
      createdAt: new Date('2024-01-15')
    },
    {
      id: 2,
      titulo: 'Diseñar dashboard',
      descripcion: 'Crear el diseño del dashboard principal con gráficos y estadísticas usando React y Tailwind CSS.',
      createdAt: new Date('2024-01-16')
    },
  ]);*/
  useEffect(() => {
    if (!token) {
      navigate('/login');
      return;
    }
    

    fetch(`${API_URL}/api/tasks`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    })
      .then((response) => {
        if (!response.ok) throw new Error('No se encontraron tareas');
        return response.json();
      })
      .then((data) => {
        setTasks(data);
      })
      .catch((err) => console.error('Error al cargar tareas:', err));
  }, [navigate, token]);


  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingTask, setEditingTask] = useState(null);

  const handleOpenModal = () => {
    setEditingTask(null);
    setIsModalOpen(true);
  };

  const handleEditTask = (task) => {
    setEditingTask(task);
    setIsModalOpen(true);
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setEditingTask(null);
  };

  const handleSubmit = (formData) => {
    if (editingTask) {
      // Editar tarea existente
      setTasks(tasks.map(task => 
        task.id === editingTask.id 
          ? { ...task, ...formData }
          : task
      ));
      reloadPage();
    } else {
      // Crear nueva tarea
      const newTask = {
        id: Date.now(),
        ...formData,
        createdAt: new Date()
      };
      setTasks([newTask, ...tasks]);
      reloadPage();
    }
  };

  const handleDeleteTask = async (id) => {
    if (confirm('¿Estás seguro de eliminar esta tarea?')) {
      //setTasks(tasks.filter(task => task.id !== id));
      try {
        const response = await fetch(`${API_URL}/api/tasks/${id}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });
  
        if (response.ok) {
          setTasks(tasks.filter((t) => t.id !== id));
          reloadPage();
        } else {
          alert('Error al eliminar la tarea');
        }
      } catch (err) {
        console.error('Error al eliminar:', err);
      }
    }
  };

  function reloadPage() {
    window.location.reload();
  }

  const completedTasks = tasks.filter(task => task.completed === true);
  const pendingTasks = tasks.filter(task => task.completed === false);
  

  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-pink-50">
      <header className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Mis Tareas</h1>
              <p className="text-gray-600 mt-1">Gestiona y organiza tus tareas diarias</p>
            </div>
            <button
              onClick={handleOpenModal}
              className="flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors shadow-lg hover:shadow-xl font-medium"
            >
              <Plus className="w-5 h-5" />
              Nueva Tarea
            </button>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div className="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600 font-medium">Total de Tareas</p>
                <p className="text-3xl font-bold text-purple-600 mt-1">{tasks.length}</p>
              </div>
              <div className="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <CheckCircle className="w-6 h-6 text-purple-600" />
              </div>
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600 font-medium">Completadas</p>
                <p className="text-3xl font-bold text-green-600 mt-1">{completedTasks.length}</p>
              </div>
              <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <CheckCircle className="w-6 h-6 text-green-600" />
              </div>
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600 font-medium">Pendientes</p>
                <p className="text-3xl font-bold text-orange-600 mt-1">{pendingTasks.length}</p>
              </div>
              <div className="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <CheckCircle className="w-6 h-6 text-orange-600" />
              </div>
            </div>
          </div>
        </div>


        {tasks.length === 0 ? (
          <div className="bg-white rounded-xl shadow-md p-12 text-center border border-gray-100">
            <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Plus className="w-8 h-8 text-gray-400" />
            </div>
            <h3 className="text-xl font-semibold text-gray-800 mb-2">No hay tareas</h3>
            <p className="text-gray-600 mb-6">Comienza creando tu primera tarea</p>
            <button
              onClick={handleOpenModal}
              className="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium"
            >
              <Plus className="w-5 h-5" />
              Crear Tarea
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {tasks.map(task => (
              <TaskCard
                key={task.id}
                task={task}
                onEdit={handleEditTask}
                onDelete={handleDeleteTask}
              />
            ))}
          </div>
        )}
      </main>

      <TaskForm
        isOpen={isModalOpen}
        onClose={handleCloseModal}
        onSubmit={handleSubmit}
        titulo={editingTask ? 'Editar Tarea' : 'Nueva Tarea'}
        initialData={editingTask}
        token={token}
      />

      <style>{`
        @keyframes fadeIn {
          from {
            opacity: 0;
          }
          to {
            opacity: 1;
          }
        }

        @keyframes scaleIn {
          from {
            opacity: 0;
            transform: scale(0.95);
          }
          to {
            opacity: 1;
            transform: scale(1);
          }
        }

        .animate-fadeIn {
          animation: fadeIn 0.2s ease-out;
        }

        .animate-scaleIn {
          animation: scaleIn 0.2s ease-out;
        }
      `}</style>
    </div>
  );
}

export default Tasks;