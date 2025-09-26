import { useState } from 'react'

const Form = ({ onSubmit, loading, onReset }) => {
  const [formData, setFormData] = useState({
    'Unit Name': '',
    'Arrival': '',
    'Departure': '',
    'Occupants': 1,
    'Ages': [25]
  })

  const [errors, setErrors] = useState({})

  const validateForm = () => {
    const newErrors = {}

    if (!formData['Unit Name'].trim()) {
      newErrors['Unit Name'] = 'Unit name is required'
    }

    if (!formData['Arrival']) {
      newErrors['Arrival'] = 'Arrival date is required'
    }

    if (!formData['Departure']) {
      newErrors['Departure'] = 'Departure date is required'
    }

    if (formData['Arrival'] && formData['Departure']) {
      const arrival = new Date(formData['Arrival'])
      const departure = new Date(formData['Departure'])
      if (arrival >= departure) {
        newErrors['Departure'] = 'Departure must be after arrival'
      }
    }

    if (formData['Occupants'] < 1) {
      newErrors['Occupants'] = 'At least 1 occupant required'
    }

    if (formData['Ages'].length !== formData['Occupants']) {
      newErrors['Ages'] = 'Number of ages must match occupants'
    }

    formData['Ages'].forEach((age, index) => {
      if (age < 1 || age > 120) {
        newErrors[`Age${index}`] = 'Age must be between 1 and 120'
      }
    })

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    if (validateForm()) {
      // Convert dates to dd/mm/yyyy format
      const submitData = {
        ...formData,
        'Arrival': formatDateForAPI(formData['Arrival']),
        'Departure': formatDateForAPI(formData['Departure'])
      }
      onSubmit(submitData)
    }
  }

  const formatDateForAPI = (dateString) => {
    const date = new Date(dateString)
    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${day}/${month}/${year}`
  }

  const handleOccupantsChange = (newOccupants) => {
    const occupants = Math.max(1, parseInt(newOccupants) || 1)
    const currentAges = [...formData['Ages']]
    
    if (occupants > currentAges.length) {
      // Add new ages (default to 25)
      while (currentAges.length < occupants) {
        currentAges.push(25)
      }
    } else if (occupants < currentAges.length) {
      // Remove excess ages
      currentAges.splice(occupants)
    }

    setFormData({
      ...formData,
      'Occupants': occupants,
      'Ages': currentAges
    })
  }

  const handleAgeChange = (index, age) => {
    const newAges = [...formData['Ages']]
    newAges[index] = Math.max(1, parseInt(age) || 1)
    setFormData({
      ...formData,
      'Ages': newAges
    })
  }

  const handleReset = () => {
    setFormData({
      'Unit Name': '',
      'Arrival': '',
      'Departure': '',
      'Occupants': 1,
      'Ages': [25]
    })
    setErrors({})
    onReset()
  }

  return (
    <div className="card shadow-xl border-0 bg-white/80 backdrop-blur-sm">
      <div className="flex items-center mb-6">
        <div className="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-3">
          <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <h2 className="text-2xl font-bold text-gray-800">Rate Query Form</h2>
      </div>
      
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Unit Name
          </label>
          <input
            type="text"
            className={`input-field ${errors['Unit Name'] ? 'border-red-500' : ''}`}
            value={formData['Unit Name']}
            onChange={(e) => setFormData({...formData, 'Unit Name': e.target.value})}
            placeholder="e.g., Deluxe Suite, Standard Room"
          />
          {errors['Unit Name'] && (
            <p className="text-red-500 text-sm mt-1">{errors['Unit Name']}</p>
          )}
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Arrival Date
            </label>
            <input
              type="date"
              className={`input-field ${errors['Arrival'] ? 'border-red-500' : ''}`}
              value={formData['Arrival']}
              onChange={(e) => setFormData({...formData, 'Arrival': e.target.value})}
            />
            {errors['Arrival'] && (
              <p className="text-red-500 text-sm mt-1">{errors['Arrival']}</p>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Departure Date
            </label>
            <input
              type="date"
              className={`input-field ${errors['Departure'] ? 'border-red-500' : ''}`}
              value={formData['Departure']}
              onChange={(e) => setFormData({...formData, 'Departure': e.target.value})}
            />
            {errors['Departure'] && (
              <p className="text-red-500 text-sm mt-1">{errors['Departure']}</p>
            )}
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Number of Occupants
          </label>
          <input
            type="number"
            min="1"
            max="10"
            className={`input-field ${errors['Occupants'] ? 'border-red-500' : ''}`}
            value={formData['Occupants']}
            onChange={(e) => handleOccupantsChange(e.target.value)}
          />
          {errors['Occupants'] && (
            <p className="text-red-500 text-sm mt-1">{errors['Occupants']}</p>
          )}
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Ages of Occupants
          </label>
          <div className="grid grid-cols-2 gap-2">
            {formData['Ages'].map((age, index) => (
              <div key={index}>
                <input
                  type="number"
                  min="1"
                  max="120"
                  className={`input-field ${errors[`Age${index}`] ? 'border-red-500' : ''}`}
                  value={age}
                  onChange={(e) => handleAgeChange(index, e.target.value)}
                  placeholder={`Person ${index + 1} age`}
                />
                {errors[`Age${index}`] && (
                  <p className="text-red-500 text-xs mt-1">{errors[`Age${index}`]}</p>
                )}
              </div>
            ))}
          </div>
          {errors['Ages'] && (
            <p className="text-red-500 text-sm mt-1">{errors['Ages']}</p>
          )}
        </div>

        <div className="flex gap-3 pt-6">
          <button
            type="submit"
            disabled={loading}
            className="btn-primary flex-1 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
          >
            {loading ? (
              <div className="flex items-center justify-center">
                <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                Querying...
              </div>
            ) : (
              <div className="flex items-center justify-center">
                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Get Rates
              </div>
            )}
          </button>
          
          <button
            type="button"
            onClick={handleReset}
            className="btn-secondary shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200"
          >
            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reset
          </button>
        </div>
      </form>
    </div>
  )
}

export default Form