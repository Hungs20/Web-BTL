import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import * as API from '../../../../api';
import FormUpdateStudentProgrammingLanguage from '../../../Form-Update/formUpdateStudentProgrammingLanguage';
import {connect} from 'react-redux';

class StudentProgrammingLanguage extends Component {
    constructor(props) {
        super(props);
        this.state={
            userInformation : props.userInformation,
            listStudentProgrammingLanguage : [],
            level : null,
            openEditForm : false
        }
    }
    componentDidMount(){
        this.getListStudentProgrammingLanguage();
    }
    
    getListStudentProgrammingLanguage = () => {
        let {userInformation} = this.state;
        API.getStudentProgrammingLanguage(userInformation.user_id).then(
            res => { if (res.status == 200 && res.statusText == "OK"){
                    this.setState({
                        listStudentProgrammingLanguage : res.data.languages,
                    })
                }
            }
        ).catch(err => {
            console.log(err);
        })
    }

    toggleEditForm = () => {
        let { openEditForm } = this.state;
        this.setState({
            openEditForm : !openEditForm
        })
    }

    removeProgramLanguage = (id) => {
        let {account} = this.props
        return API.removeProgramLanguage(account,id)
        .then(res=>{
            alert(res.data.message);
            this.getListStudentProgrammingLanguage();
        })
        .catch(err=>{
            console.log(err);
            alert('lỗi');
        })
    }
    
    render() {
        let { userInformation } = this.props
        let {openEditForm, listStudentProgrammingLanguage} = this.state

        let listItems = listStudentProgrammingLanguage.map((d, index) => 
           <div className="list-student-programming-languages" key={index}>
                <h4>
                    {d.program_language_name}
                    <div style={{ display: 'inline-block', cursor: 'pointer' }}
                         onClick={() => this.removeProgramLanguage(d.id)}>
                             <i className="fa fa-trash"></i>
                    </div>
                </h4>
                <p>
                    <i style={{ fontStyle: 'italic' }}>{d.level} years of experience.</i>
                </p>
            </div>
        )
        return (
            <div className="user-profile-ov st2">
                <h3>
                    <div style={{ display: 'inline-block', cursor: 'pointer' }} className="exp-bx-open">
                        Programming languages
                    </div>
                    {userInformation.my_profile ? <div style={{ display: 'inline-block', cursor: 'pointer' }}
                         onClick={this.toggleEditForm}>
                             <i className="fa fa-pencil"></i>
                    </div> : ''}
                </h3>
                <div>
                    <div>
                    {listItems }
                    </div>
                    </div>
                {openEditForm?
                    <FormUpdateStudentProgrammingLanguage toggleEditForm={this.toggleEditForm}
                                                regetData={this.getListStudentProgrammingLanguage}/>
                :''}
            </div>
        )
    }
}


const mapStateToProps = state => {
    return {
        account: state.account
    }
}

export default connect(mapStateToProps,null)(StudentProgrammingLanguage)