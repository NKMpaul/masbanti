import { useState } from 'react'
import { Table, Card, Select, Input, DatePicker, Row, Col, Tag } from 'antd'
import { useQuery } from '@tanstack/react-query'
import api from '../services/api'

const { RangePicker } = DatePicker

interface AuditLog {
  id: string
  action: string
  model: string
  model_id: string
  old_values: Record<string, unknown>
  new_values: Record<string, unknown>
  ip_address: string
  created_at: string
  user: { name: string; email: string }
}

const actionColors: Record<string, string> = {
  CREATE: 'green',
  UPDATE: 'orange',
  DELETE: 'red',
}

export default function AuditLogs() {
  const [filters, setFilters] = useState({
    action: '',
    model: '',
    date_debut: '',
    date_fin: '',
  })

  const { data, isLoading } = useQuery({
    queryKey: ['audit-logs', filters],
    queryFn: () => api.get('/audit-logs', { params: filters }).then(res => res.data)
  })

  const columns = [
    {
      title: 'Date', dataIndex: 'created_at', key: 'created_at',
      render: (d: string) => d?.slice(0, 19).replace('T', ' ')
    },
    {
      title: 'Utilisateur', key: 'user',
      render: (_: unknown, r: AuditLog) => r.user?.name ?? 'Système'
    },
    {
      title: 'Action', dataIndex: 'action', key: 'action',
      render: (action: string) => <Tag color={actionColors[action]}>{action}</Tag>
    },
    { title: 'Modèle', dataIndex: 'model', key: 'model' },
    { title: 'IP', dataIndex: 'ip_address', key: 'ip_address' },
    {
      title: 'Détails', key: 'details',
      render: (_: unknown, r: AuditLog) => (
        <div style={{ fontSize: 11 }}>
          {r.action === 'CREATE' && (
            <span style={{ color: 'green' }}>
              {Object.keys(r.new_values ?? {}).slice(0, 3).join(', ')}...
            </span>
          )}
          {r.action === 'UPDATE' && (
            <span style={{ color: 'orange' }}>
              Modifié : {Object.keys(r.new_values ?? {}).join(', ')}
            </span>
          )}
          {r.action === 'DELETE' && (
            <span style={{ color: 'red' }}>
              Supprimé : {r.model_id}
            </span>
          )}
        </div>
      )
    },
  ]

  return (
    <div>
      <h2>Journal d'Audit</h2>

      <Card style={{ marginBottom: 16 }}>
        <Row gutter={16}>
          <Col span={4}>
            <Select
              placeholder="Action"
              style={{ width: '100%' }}
              allowClear
              onChange={v => setFilters(f => ({ ...f, action: v ?? '' }))}
              options={[
                { value: 'CREATE', label: 'Création' },
                { value: 'UPDATE', label: 'Modification' },
                { value: 'DELETE', label: 'Suppression' },
              ]}
            />
          </Col>
          <Col span={4}>
            <Input
              placeholder="Modèle (ex: Agence)"
              onChange={e => setFilters(f => ({ ...f, model: e.target.value }))}
            />
          </Col>
          <Col span={8}>
            <RangePicker
              style={{ width: '100%' }}
              onChange={dates => {
                if (dates) {
                  setFilters(f => ({
                    ...f,
                    date_debut: dates[0]!.format('YYYY-MM-DD'),
                    date_fin: dates[1]!.format('YYYY-MM-DD'),
                  }))
                } else {
                  setFilters(f => ({ ...f, date_debut: '', date_fin: '' }))
                }
              }}
            />
          </Col>
        </Row>
      </Card>

      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
        pagination={{ pageSize: 20 }}
      />
    </div>
  )
}