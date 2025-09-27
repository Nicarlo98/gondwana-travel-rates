import { useState } from 'react'
import Form from './components/Form'
import Result from './components/Result'

function App() {
  const [result, setResult] = useState(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)

  const handleFormSubmit = async (formData) => {
    setLoading(true)
    setError(null)
    setResult(null)

    try {
      console.log('Sending request to backend:', formData)

      const response = await fetch('http://localhost:8000/api/rates.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      })

      console.log('Response status:', response.status)

      if (!response.ok) {
        const errorText = await response.text()
        console.error('Error response:', errorText)

        try {
          const errorData = JSON.parse(errorText)
          throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`)
        } catch (parseError) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`)
        }
      }

      const data = await response.json()
      console.log('Success response:', data)
      setResult(data)

    } catch (err) {
      console.error('Request failed:', err)
      if (err.name === 'TypeError' && err.message.includes('fetch')) {
        setError('Cannot connect to backend server. Make sure it\'s running on http://localhost:8000')
      } else {
        setError(err.message)
      }
    } finally {
      setLoading(false)
    }
  }

  const handleReset = () => {
    setResult(null)
    setError(null)
  }

  // const testConnection = async () => {
  //   try {
  //     console.log('Testing backend connection...')
  //     const response = await fetch('http://localhost:8000/api/test.php')
  //     const data = await response.json()
  //     console.log('Connection test successful:', data)
  //     alert('Backend connection successful!')
  //   } catch (err) {
  //     console.error('Connection test failed:', err)
  //     alert('Backend connection failed: ' + err.message)
  //   }
  // }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
      {/* Background decoration */}
      <div className="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] -z-10"></div>

      <div className="relative z-10 py-12">
        <div className="max-w-6xl mx-auto px-4">
          <header className="text-center mb-12">
            <div className="mb-6">
              <h1 className="text-5xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent mb-4">
                Rates API Assessment
              </h1>
              <div className="w-32 h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-500 mx-auto rounded-full mb-6"></div>
              <p className="text-xl text-gray-700 max-w-3xl mx-auto leading-relaxed">
                Discover competitive accommodation rates with our intelligent booking system.
                Simply enter your travel details and get instant pricing with real-time availability.
              </p>
            </div>
          </header>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div className="transform hover:scale-[1.02] transition-transform duration-200">
              <Form
                onSubmit={handleFormSubmit}
                loading={loading}
                onReset={handleReset}
              />
            </div>

            <div className="transform hover:scale-[1.02] transition-transform duration-200">
              <Result
                result={result}
                error={error}
                loading={loading}
              />
            </div>
          </div>

          <footer className="text-center">
            <div className="inline-flex items-center px-6 py-3 bg-white/70 backdrop-blur-sm rounded-full shadow-lg border border-white/20">
              <div className="flex items-center space-x-2 text-gray-600">
                <svg className="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
                <span className="font-medium">Built with React + Vite and PHP backend By Klievett N Abrahams  </span>
              </div>
            </div>
          </footer>
        </div>
      </div>
    </div>
  )
}

export default App