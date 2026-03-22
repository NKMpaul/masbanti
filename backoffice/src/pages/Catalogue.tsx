import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Select, Space, Popconfirm, message, Tabs } from 'antd'
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'

interface TypeArticle {
  id: string
  nom: string
  categorie: string
  prix_base: number
}

interface TypeService {
  id: string
  nom: string
  description: string
  coefficient_prix: number
}

export default function Catalogue() {
  const [isArticleModalOpen, setIsArticleModalOpen] = useState(false)
  const [isServiceModalOpen, setIsServiceModalOpen] = useState(false)
  const [editingArticle, setEditingArticle] = useState<TypeArticle | null>(null)
  const [editingService, setEditingService] = useState<TypeService | null>(null)
  const [articleForm] = Form.useForm()
  const [serviceForm] = Form.useForm()
  const queryClient = useQueryClient()

  const { data: typeArticles, isLoading: loadingArticles } = useQuery({
    queryKey: ['type-articles'],
    queryFn: () => api.get('/type-articles').then(res => res.data)
  })

  const { data: typeServices, isLoading: loadingServices } = useQuery({
    queryKey: ['type-services'],
    queryFn: () => api.get('/type-services').then(res => res.data)
  })

  const createArticleMutation = useMutation({
    mutationFn: (values: TypeArticle) => api.post('/type-articles', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['type-articles'] })
      message.success('Article créé avec succès !')
      setIsArticleModalOpen(false)
      articleForm.resetFields()
    },
    onError: () => message.error('Erreur lors de la création.')
  })

  const updateArticleMutation = useMutation({
    mutationFn: (values: TypeArticle) => api.put(`/type-articles/${editingArticle?.id}`, values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['type-articles'] })
      message.success('Article modifié avec succès !')
      setIsArticleModalOpen(false)
      articleForm.resetFields()
      setEditingArticle(null)
    },
    onError: () => message.error('Erreur lors de la modification.')
  })

  const deleteArticleMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/type-articles/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['type-articles'] })
      message.success('Article supprimé avec succès !')
    }
  })

  const createServiceMutation = useMutation({
    mutationFn: (values: TypeService) => api.post('/type-services', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['type-services'] })
      message.success('Service créé avec succès !')
      setIsServiceModalOpen(false)
      serviceForm.resetFields()
    },
    onError: () => message.error('Erreur lors de la création.')
  })

  const updateServiceMutation = useMutation({
    mutationFn: (values: TypeService) => api.put(`/type-services/${editingService?.id}`, values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['type-services'] })
      message.success('Service modifié avec succès !')
      setIsServiceModalOpen(false)
      serviceForm.resetFields()
      setEditingService(null)
    },
    onError: () => message.error('Erreur lors de la modification.')
  })

  const deleteServiceMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/type-services/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['type-services'] })
      message.success('Service supprimé avec succès !')
    }
  })

  const articleColumns = [
    { title: 'Nom', dataIndex: 'nom', key: 'nom' },
    { title: 'Catégorie', dataIndex: 'categorie', key: 'categorie' },
    {
      title: 'Prix de base', dataIndex: 'prix_base', key: 'prix_base',
      render: (prix: number) => `${prix} DH`
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: TypeArticle) => (
        <Space>
          <Button icon={<EditOutlined />} onClick={() => {
            setEditingArticle(record)
            articleForm.setFieldsValue(record)
            setIsArticleModalOpen(true)
          }} />
          <Popconfirm
            title="Supprimer cet article ?"
            onConfirm={() => deleteArticleMutation.mutate(record.id)}
            okText="Oui" cancelText="Non"
          >
            <Button icon={<DeleteOutlined />} danger />
          </Popconfirm>
        </Space>
      )
    }
  ]

  const serviceColumns = [
    { title: 'Nom', dataIndex: 'nom', key: 'nom' },
    { title: 'Description', dataIndex: 'description', key: 'description' },
    {
      title: 'Coefficient', dataIndex: 'coefficient_prix', key: 'coefficient_prix',
      render: (coef: number) => `x${coef}`
    },
    {
      title: 'Actions', key: 'actions',
      render: (_: unknown, record: TypeService) => (
        <Space>
          <Button icon={<EditOutlined />} onClick={() => {
            setEditingService(record)
            serviceForm.setFieldsValue(record)
            setIsServiceModalOpen(true)
          }} />
          <Popconfirm
            title="Supprimer ce service ?"
            onConfirm={() => deleteServiceMutation.mutate(record.id)}
            okText="Oui" cancelText="Non"
          >
            <Button icon={<DeleteOutlined />} danger />
          </Popconfirm>
        </Space>
      )
    }
  ]

  const tabItems = [
    {
      key: 'articles',
      label: 'Types d\'articles',
      children: (
        <div>
          <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
            <Button type="primary" icon={<PlusOutlined />} onClick={() => {
              setEditingArticle(null)
              articleForm.resetFields()
              setIsArticleModalOpen(true)
            }}>
              Nouvel Article
            </Button>
          </div>
          <Table
            columns={articleColumns}
            dataSource={typeArticles}
            rowKey="id"
            loading={loadingArticles}
          />
        </div>
      )
    },
    {
      key: 'services',
      label: 'Types de services',
      children: (
        <div>
          <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
            <Button type="primary" icon={<PlusOutlined />} onClick={() => {
              setEditingService(null)
              serviceForm.resetFields()
              setIsServiceModalOpen(true)
            }}>
              Nouveau Service
            </Button>
          </div>
          <Table
            columns={serviceColumns}
            dataSource={typeServices}
            rowKey="id"
            loading={loadingServices}
          />
        </div>
      )
    }
  ]

  return (
    <div>
      <h2>Catalogue</h2>

      <Tabs items={tabItems} />

      {/* Modal Article */}
      <Modal
        title={editingArticle ? 'Modifier Article' : 'Nouvel Article'}
        open={isArticleModalOpen}
        onCancel={() => setIsArticleModalOpen(false)}
        onOk={() => articleForm.submit()}
        confirmLoading={createArticleMutation.isPending || updateArticleMutation.isPending}
      >
        <Form form={articleForm} layout="vertical" onFinish={(values) =>
          editingArticle ? updateArticleMutation.mutate(values) : createArticleMutation.mutate(values)
        }>
          <Form.Item label="Nom" name="nom" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Catégorie" name="categorie" rules={[{ required: true }]}>
            <Select options={[
              { value: 'VETEMENT', label: 'Vêtement' },
              { value: 'LINGE_MAISON', label: 'Linge de maison' },
              { value: 'TEXTILE_SPECIAL', label: 'Textile spécial' },
            ]} />
          </Form.Item>
          <Form.Item label="Prix de base (DH)" name="prix_base" rules={[{ required: true }]}>
            <Input type="number" min={0} />
          </Form.Item>
        </Form>
      </Modal>

      {/* Modal Service */}
      <Modal
        title={editingService ? 'Modifier Service' : 'Nouveau Service'}
        open={isServiceModalOpen}
        onCancel={() => setIsServiceModalOpen(false)}
        onOk={() => serviceForm.submit()}
        confirmLoading={createServiceMutation.isPending || updateServiceMutation.isPending}
      >
        <Form form={serviceForm} layout="vertical" onFinish={(values) =>
          editingService ? updateServiceMutation.mutate(values) : createServiceMutation.mutate(values)
        }>
          <Form.Item label="Nom" name="nom" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item label="Description" name="description">
            <Input.TextArea />
          </Form.Item>
          <Form.Item label="Coefficient prix" name="coefficient_prix" rules={[{ required: true }]}>
            <Input type="number" min={0} step={0.1} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}