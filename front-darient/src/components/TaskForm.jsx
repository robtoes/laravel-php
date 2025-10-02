import React, { useState , useEffect} from 'react';
import { X, Plus, CheckCircle, Trash2, Edit } from 'lucide-react';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080';

const TaskForm = ({ task, token, isOpen, onClose, onSubmit, titulo = 'Nueva Tarea', initialData = null }) => {
    const [formData, setFormData] = useState({
        title: initialData?.title || '',
        description: initialData?.description || '',  
        completed: initialData?.completed || false, 
    });
    const [errors, setErrors] = useState({});

    useEffect(() => {
        if (initialData) {
          setFormData({
            title: initialData.title || '',
            description: initialData.description || '',
            completed: initialData?.completed || 0, 
          });
        } else {
          setFormData({
            title: '',
            description: '',
            completed: 0,
          });
        }
      }, [initialData]);

    const handleChange = (e) => {
      const { name, value, checked } = e.target;
      setFormData(prev => ({
        ...prev,
        [name]: value,
        [name]: name === 'completed' ? checked : value,
      }));
      // Limpiar error del campo cuando el usuario escribe
      if (errors[name]) {
        setErrors(prev => ({
          ...prev,
          [name]: '',
          [name]: name === 'completed' ? checked : value,
        }));
      }
    };

    const validate = () => {
      const newErrors = {};
      if (!formData.title.trim()) {
        newErrors.titulo = 'El título es obligatorio';
      }
      if (!formData.description.trim()) {
        newErrors.descripcion = 'La descripción es obligatoria';
      }
      return newErrors;
    };
  
    const handleSubmit = async (e) => {
        e.preventDefault();
        const newErrors = validate();
      
      if (Object.keys(newErrors).length > 0) {
        setErrors(newErrors);
        return;
      }

      let completedCheck = formData.completed == true ? 1 : 0;
    
        try {
          const url = initialData ? `${API_URL}/api/tasks/update/${initialData.id}` : `${API_URL}/api/tasks`;
    
          const response = await fetch(url, {
            method: 'POST' ,
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ title: formData.title, description: formData.description, completed: completedCheck}),
          });
    
          const data = await response.json();
    
          if (response.ok) {
            onSubmit(formData);
            handleClose();
          } else {
            setErrors(data.message || 'Error al guardar la tarea');
          }
        } catch (err) {
            setErrors('Error de red');
        }
      };

    const handleClose = () => {
      setFormData({ title: '', description: '' });
      setErrors({});
      onClose();
    };
  
    if (!isOpen) return null;
  
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 animate-fadeIn">
        <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all animate-scaleIn">
          <div className="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 className="text-2xl font-bold text-gray-800">{titulo}</h2>
            <button
              onClick={handleClose}
              className="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <X className="w-6 h-6" />
            </button>
          </div>
  
          <div className="p-6 space-y-4">
            <div>
              <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-2">
                Título <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                id="title"
                name="title"
                value={formData.title}
                onChange={handleChange}
                className={`w-full px-4 py-3 border text-gray-700 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition ${
                  errors.titulo ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder="Ingresa el título de la tarea"
              />
              {errors.titulo && (
                <p className="mt-1 text-sm text-red-600">{errors.titulo}</p>
              )}
            </div>
  
            <div>
              <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                Descripción <span className="text-red-500">*</span>
              </label>
              <textarea
                id="description"
                name="description"
                value={formData.description}
                onChange={handleChange}
                rows={4}
                className={`w-full text-gray-700 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition resize-none ${
                  errors.descripcion ? 'border-red-500' : 'border-gray-300'
                }`}
                placeholder="Describe la tarea con detalle"
              />
              {errors.descripcion && (
                <p className="mt-1 text-sm text-red-600">{errors.descripcion}</p>
              )}
            </div>
            {initialData ? (
            <div className="flex flex-wrap -mx-4 mb-6 items-center justify-right">
                <div className="w-full lg:w-auto px-4 mb-4 lg:mb-0 text-gray-700">
                <label>
                <input
                    type="checkbox"
                    name="completed"
                    checked={formData.completed}
                    onChange={handleChange}
                />
                    <span className="ml-1">Completado</span>
                    </label>
                </div>
            </div>
            ) :(<div> </div>)}
          </div>
  
          <div className="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
            <button
              onClick={handleClose}
              className="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
            >
              Cancelar
            </button>
            <button
              onClick={handleSubmit}
              className="px-6 py-2.5 text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition-colors font-medium flex items-center gap-2"
            >
              <CheckCircle className="w-4 h-4" />
              Aceptar
            </button>
          </div>
        </div>
      </div>
    );
  };
  

export default TaskForm;