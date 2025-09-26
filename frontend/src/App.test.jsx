import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import App from './App'

describe('App', () => {
  it('renders the main heading', () => {
    render(<App />)
    expect(screen.getByText('Rates API Assessment')).toBeInTheDocument()
  })

  it('renders the form and result sections', () => {
    render(<App />)
    expect(screen.getByText('Rate Query Form')).toBeInTheDocument()
    expect(screen.getByText('Query Result')).toBeInTheDocument()
  })
})