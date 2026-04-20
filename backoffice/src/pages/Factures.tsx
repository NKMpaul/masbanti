import { useState } from 'react'
import { Table, Button, Modal, Form, Input, Select, Space, message, Tag } from 'antd'
import { PlusOutlined, EyeOutlined,DownloadOutlined } from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '../services/api'


interface Facture {
  id: string
  numero_facture: string
  montant_ht: number
  tva: number
  remise_fidelite: number
  montant_ttc: number
  statut: string
  date_emission: string
  commande: { numero_commande: string }
  client: { nom: string; prenom: string }
  agence: { nom: string }
}

const statutColors: Record<string, string> = {
  EN_ATTENTE: 'orange',
  PAYEE: 'green',
  PARTIELLEMENT_PAYEE: 'blue',
  ANNULEE: 'red',
}

export default function Factures() {
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isPaiementModalOpen, setIsPaiementModalOpen] = useState(false)
  const [selectedFacture, setSelectedFacture] = useState<Facture | null>(null)
  const [form] = Form.useForm()
  const [paiementForm] = Form.useForm()
  const queryClient = useQueryClient()

  const { data, isLoading } = useQuery({
    queryKey: ['factures'],
    queryFn: () => api.get('/factures').then(res => res.data)
  })

  const { data: commandes } = useQuery({
    queryKey: ['commandes'],
    queryFn: () => api.get('/commandes').then(res => res.data)
  })

  const { data: clients } = useQuery({
    queryKey: ['clients'],
    queryFn: () => api.get('/clients').then(res => res.data)
  })

  const { data: agences } = useQuery({
    queryKey: ['agences'],
    queryFn: () => api.get('/agences').then(res => res.data)
  })

  const createMutation = useMutation({
    mutationFn: (values: Facture) => api.post('/factures', values),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['factures'] })
      message.success('Facture créée avec succès !')
      setIsModalOpen(false)
      form.resetFields()
    },
    onError: () => message.error('Erreur lors de la création.')
  })

  const paiementMutation = useMutation({
    mutationFn: (values: { montant: number; mode_paiement: string; reference: string }) =>
      api.post('/paiements', { ...values, facture_id: selectedFacture?.id }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['factures'] })
      message.success('Paiement enregistré avec succès !')
      setIsPaiementModalOpen(false)
      paiementForm.resetFields()
    },
    onError: () => message.error('Erreur lors du paiement.')
  })

  const columns = [
    { title: 'Numéro', dataIndex: 'numero_facture', key: 'numero_facture' },
    {
      title: 'Commande', key: 'commande',
      render: (_: unknown, record: Facture) => record.commande?.numero_commande
    },
    {
      title: 'Client', key: 'client',
      render: (_: unknown, record: Facture) =>
        `${record.client?.nom} ${record.client?.prenom}`
    },
    {
      title: 'Montant TTC', dataIndex: 'montant_ttc', key: 'montant_ttc',
      render: (montant: number) => `${montant} DH`
    },
    {
      title: 'Statut', dataIndex: 'statut', key: 'statut',
      render: (statut: string) => (
        <Tag color={statutColors[statut]}>{statut}</Tag>
      )
    },
   
    {
  title: 'Actions', key: 'actions',
  render: (_: unknown, record: Facture) => (
    <Space>
      <Button
        icon={<EyeOutlined />}
        onClick={() => {
          setSelectedFacture(record)
          setIsPaiementModalOpen(true)
        }}
        disabled={record.statut === 'PAYEE' || record.statut === 'ANNULEE'}
      >
        Payer
      </Button>
      <Button
  icon={<DownloadOutlined />}
  onClick={async () => {
    try {
      message.loading({ content: 'Génération du PDF...', key: 'telechargement_pdf' });
      console.log("Étape 1 : Envoi de la requête à Laravel pour la facture ID :", record.id);

      // 1. Appel via ton instance Axios unifiée
      const response = await api.get(`/factures/${record.id}/pdf`, {
        responseType: 'blob', // 👈 TRÈS IMPORTANT pour forcer Axios à lire un binaire
        headers: {
          Accept: 'application/pdf',
        },
      });

      console.log("Étape 2 : Réponse reçue du serveur. Taille du fichier :", response.data.size);

      // Vérification de sécurité : si le backend s'est trompé et a envoyé du JSON (erreur) au lieu d'un PDF
      if (response.data.type === 'application/json') {
        const text = await response.data.text();
        const errorJson = JSON.parse(text);
        console.error("Le serveur a renvoyé une erreur JSON au lieu d'un PDF :", errorJson);
        message.error({ content: errorJson.message || 'Erreur serveur Laravel.', key: 'telechargement_pdf' });
        return;
      }

      // 2. Création du Blob et du lien de téléchargement
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      
      // On force l'affichage du bouton invisible dans le DOM pour que le clic fonctionne sur tous les navigateurs
      a.style.display = 'none'; 
      a.href = url;
      a.download = `facture-${record.numero_facture}.pdf`;
      
      document.body.appendChild(a);
      a.click(); // 👈 Le déclencheur du téléchargement

      console.log("Étape 3 : Clic simulé avec succès pour le fichier :", a.download);

      // 3. Nettoyage de la mémoire
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);

      message.success({ content: 'Téléchargement réussi !', key: 'telechargement_pdf' });
   } catch (error: unknown) { // 👈 On utilise 'unknown' à la place de 'any'
  console.error("Erreur attrapée pendant le processus :", error);

  // On vérifie si l'objet possède bien la structure d'une erreur standard
  if (error instanceof Error) {
    console.error("Message d'erreur :", error.message);
  }

  message.error({ content: 'Erreur lors du téléchargement du PDF.', key: 'telechargement_pdf' });
}
  }}
>
  PDF
</Button>
    </Space>
  )
}


  ]

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16 }}>
        <h2>Gestion des Factures</h2>
        <Button type="primary" icon={<PlusOutlined />} onClick={() => setIsModalOpen(true)}>
          Nouvelle Facture
        </Button>
      </div>

      <Table
        columns={columns}
        dataSource={data?.data}
        rowKey="id"
        loading={isLoading}
      />

      <Modal
        title="Nouvelle Facture"
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={createMutation.isPending}
      >
        <Form form={form} layout="vertical" onFinish={(values) => createMutation.mutate(values)}>
          <Form.Item label="Commande" name="commande_id" rules={[{ required: true }]}>
            <Select
              options={commandes?.data?.map((c: { id: string; numero_commande: string }) => ({
                value: c.id,
                label: c.numero_commande
              }))}
            />
          </Form.Item>
          <Form.Item label="Client" name="client_id" rules={[{ required: true }]}>
            <Select
              options={clients?.data?.map((c: { id: string; nom: string; prenom: string }) => ({
                value: c.id,
                label: `${c.nom} ${c.prenom}`
              }))}
            />
          </Form.Item>
          <Form.Item label="Agence" name="agence_id" rules={[{ required: true }]}>
            <Select
              options={agences?.data?.map((a: { id: string; nom: string }) => ({
                value: a.id,
                label: a.nom
              }))}
            />
          </Form.Item>
          <Form.Item label="Montant HT (DH)" name="montant_ht" rules={[{ required: true }]}>
            <Input type="number" min={0} />
          </Form.Item>
          <Form.Item label="TVA (%)" name="tva" rules={[{ required: true }]}>
            <Input type="number" min={0} />
          </Form.Item>
          <Form.Item label="Remise fidélité (DH)" name="remise_fidelite">
            <Input type="number" min={0} />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={`Enregistrer paiement — ${selectedFacture?.numero_facture}`}
        open={isPaiementModalOpen}
        onCancel={() => setIsPaiementModalOpen(false)}
        onOk={() => paiementForm.submit()}
        confirmLoading={paiementMutation.isPending}
      >
        <Form form={paiementForm} layout="vertical" onFinish={(values) => paiementMutation.mutate(values)}>
          <Form.Item label="Montant (DH)" name="montant" rules={[{ required: true }]}>
            <Input type="number" min={0} />
          </Form.Item>
          <Form.Item label="Mode de paiement" name="mode_paiement" rules={[{ required: true }]}>
            <Select
              options={[
                { value: 'ESPECES', label: 'Espèces' },
                { value: 'CARTE_BANCAIRE', label: 'Carte bancaire' },
                { value: 'PAIEMENT_MOBILE', label: 'Paiement mobile' },
                { value: 'VIREMENT', label: 'Virement' },
              ]}
            />
          </Form.Item>
          <Form.Item label="Référence" name="reference">
            <Input />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}